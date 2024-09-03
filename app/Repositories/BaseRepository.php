<?php

namespace App\Repositories;

use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class BaseRepository
 */
abstract class BaseRepository
{
    /** @var int */
    protected $perPage = 12;
    /** @var Model */
    protected $model;
    /** @var array */
    protected $filterWhereColumns = [];
    /** @var array */
    protected $filterHavingColumns = [];
    /** @var array */
    protected $searchWhereColumns = [];
    /** @var array */
    protected $searchHavingColumns = [];

    public $processCache = true;

    /**
     * @param string $orderBy
     *
     * @return Collection|static[]
     */
    public function all(string $orderBy = null, string $direction = 'asc'): Collection
    {
        return Cache::remember(
            'all_' . get_class($this->model) . "{$orderBy}_{$direction}",
            1200,
            function () use ($orderBy, $direction) {
                return $orderBy
                    ? $this->model->orderBy($orderBy, $direction)->get()
                    : $this->model->get();
            }
        );
    }

    /**
     * Return paginated results of the given model from the database
     *
     *
     * @throws BadRequestHttpException
     *
     * @return LengthAwarePaginator
     */
    public function list(array $params = [], array $relationships = [])
    {
        $query = $this->model->newQuery();

        if (!empty($params['status_id'])) {
            $query->where(['status_id' => $params['status_id']]);
        }

        return $this->processList($query, $params, $relationships);
    }

    public function create(array $data): Model
    {
        $model = $this->model->newInstance();
        $model->fill($data);

        try {
            $model->save();
            if (isset($data['img'])) {
                $model->clearMediaCollection('cover');
                $model->addMedia($data['img'])->toMediaCollection('cover');
            }
            if (isset($data['svg'])) {
                $model->clearMediaCollection('svg');
                $model->addMedia($data['svg'])->toMediaCollection('svg');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $model;
    }

    /**
     * @throws BadRequestHttpException
     *
     * @return LengthAwarePaginator
     */
    protected function processList(Builder $query, array $params = [], array $relationships = [])
    {
        if ($relationships) {
            $query->with($relationships);
        }

        $query = $this->applyFilters($query, $params);

        if (!empty($params['search'])) {
            $query = $this->applySearch($query, $params['search']);
        }

        $orderBy = $params['order_by'] ?? 'id';
        $order = $params['order'] ?? 'desc';
        $perPage = $params['per_page'] ?? $this->perPage;
        $page = $params['page'] ?? 1;


        //		dd($query->dd());

        if (isset($params['cache_key'])) {
            $items = Cache::remember($params['cache_key'], 1200, function () use ($query, $orderBy, $order) {
                return $query
                    ->orderBy($orderBy, $order)
                    ->get();
            });
        } elseif ($this->processCache == true) {
            $items = Cache::remember(vsprintf(
                str_replace('?', '%s', str_replace('?', "'?'", $query->toSql())),
                $query->getBindings()
            ), 1200, function () use ($query, $orderBy, $order) {
                return $query
                    ->orderBy($orderBy, $order)
                    ->get();
            });
        } else {
            $items = $query
                ->orderBy($orderBy, $order)
                ->get();
        }

        //		dd($items);
        $result = $items
            ->slice(($page - 1) * $perPage, $perPage)
            ->all();

        return new \Illuminate\Pagination\LengthAwarePaginator(array_values($result), count($items), $perPage);
    }

    /**
     * Return total of search results of the given model from the database
     */
    public function searchTotal(array $params = []): int
    {
        $query = $this->model->newQuery();

        if ($params) {
            $query = $this->applySearch($query, $params['search']);
        }

        return $query->count();
    }

    /**
     * Return a model by ID from the database. If relationships are provided, eager load those relationships.
     *
     *
     * @return Model|null
     */
    public function find(int $id, array $relationships = [])
    {
        return $this->model
            ->with($relationships)
            ->findOrFail($id);
    }

    /**
     * @return Builder[]|Collection|Model[]|\Illuminate\Support\Collection
     */
    public function findBy(string $column, string $value, array $relationships = [])
    {
        return $this->model
            ->with($relationships)
            ->where($column, $value)
            ->get();
    }

    /**
     * @return Builder[]|Collection|Model[]|\Illuminate\Support\Collection
     */
    public function findWhere(array $where, array $relationships = [])
    {
        return $this->model
            ->with($relationships)
            ->where($where)
            ->get();
    }

    /**
     * Create a new Eloquent Query Builder instance
     */
    public function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Update model
     *
     *
     * @return bool
     */
    public function update(int $id, array $data)
    {

        $model = $this->find($id);
        if ($model->slug) {
            $model->slug = null;
        }
        $res = $model->update($data);
        if (isset($data['img'])) {
            $model->clearMediaCollection('img');
            $model->addMedia($data['img'])->toMediaCollection('img');
        }

        return $res;
    }

    public function updateOrCreate(array $attributes, array $data)
    {
        return $this->newQuery()->updateOrCreate($attributes, $data);
    }

    public function firstOrNew(array $attributes, array $data)
    {
        return $this->newQuery()->firstOrNew($attributes, $data);
    }

    /**
     * @return bool|null
     */
    public function butchUpdate(array $ids, array $data)
    {
        return $this->model->whereIn('id', $ids)->update($data);
    }

    /**
     * @throws Exception
     *
     * @return bool|null
     */
    public function butchDelete(array $ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    /**
     * @throws Exception
     *
     * @return bool|null
     */
    public function delete(int $id)
    {
        return $this->find($id)->delete();
    }

    /**
     * @throws Exception
     *
     * @return bool|null
     */
    public function deleteWhere(array $where)
    {
        return $this->model->where($where)->delete();
    }

    /**
     * @throws Exception
     *
     * @return bool|null
     */
    public function deleteModel(Model $model)
    {
        return $model->delete();
    }

    /**
     * Delete all models
     */
    public function deleteAll(array $where = []): ?bool
    {
        $query = $this->newQuery();

        if ($where) {
            $query->where($where);
        }

        return $query
            ->delete();
    }

    /**
     * @param int $id
     */
    public function generateCode(
        string $name,
        int $number,
        int $lettersCount = 3,
        int $digitsCount = 3,
        string $delimiter = '',
        string $prefix = '',
        string $field = 'code',
        int $id = null,
    ): string {
        $code = $prefix . strtoupper(substr(preg_replace('/\W/', '', $name), 0, $lettersCount)) . $delimiter;
        //
        //        $query = $this->newQuery()
        //            ->where($field, 'like', '%'.$code.'%');
        //
        //        if ($id) {
        //            $query->where('id', '!=', $id);
        //        }
        //
        //        $exists = $query->latest('id')
        //            ->first();
        //
        //        if ($exists) {
        //            if ($delimiter) {
        //                $parts = explode($delimiter, $exists->{$field});
        //                $number = end($parts);
        //            } else {
        //                $number = intval(substr($exists->{$field}, -$digitsCount));
        //            }
        //        } else {
        //            $number = 0;
        //        }

        $code .= str_pad(++$number, $digitsCount, '0', STR_PAD_LEFT);

        return $code;
    }

    /**
     * @throws BadRequestHttpException
     */
    protected function applyFilters(Builder $query, array $params): Builder
    {
        if (!isset($params['filter']) || !$params['filter']['value']) {
            return $query;
        }

        $column = $params['filter']['column'];
        $value = $params['filter']['value'];

        if (!isset($this->filterWhereColumns[$column]) && !isset($this->filterHavingColumns[$column])) {
            throw new BadRequestHttpException($this->getFilterErrorMessage());
        }

        $columnConditions = $this->filterWhereColumns[$column] ?? null;

        if ($columnConditions) {
            $query->where(function ($query) use ($columnConditions, $value) {
                /* @var Builder $query */

                if (is_array($columnConditions)) {
                    $this->applyComplexFilterWhereColumnConditions($query, $columnConditions, $value);
                } else {
                    $query->where($columnConditions, 'like', '%' . $value . '%');
                }
            });
        }

        $columnConditions = $this->filterHavingColumns[$column] ?? null;

        if ($columnConditions) {
            $query->having(DB::raw($columnConditions), 'like', '%' . $value . '%');
        }

        return $query;
    }

    /**
     * Apply complex where filter to query
     */
    protected function applyComplexFilterWhereColumnConditions(
        Builder $query,
        array $columnConditions,
        string $value,
    ): void {
        [$column, $operator] = $columnConditions;

        $valueType = $columnConditions[2] ?? null;
        $delimiter = $columnConditions[3] ?? null;

        $value = $this->prepareFilterValue($value, $valueType, $delimiter);

        switch ($operator) {
            case 'between':
                $query->whereBetween(DB::raw($column), $value);
                break;
            default:
                if (is_array($value)) {
                    foreach ($value as $item) {
                        $query->orWhere($column, 'like', '%' . $item . '%');
                    }
                } else {
                    $query->where($column, 'like', '%' . $value . '%');
                }
                break;
        }
    }

    /**
     * @param string $type
     * @param string $delimiter
     *
     * @return mixed
     */
    protected function prepareFilterValue(string $value, string $type = null, string $delimiter = null)
    {
        if ($delimiter) {
            $value = explode($delimiter, $value);
        }

        switch ($type) {
            case 'timestamps':
                if (is_array($value)) {
                    $value[0] = Carbon::createFromTimestampUTC($value[0])->startOfDay();
                    $value[1] = Carbon::createFromTimestampUTC($value[1])->endOfDay();
                } else {
                    $value = Carbon::createFromTimestampUTC($value);
                }
                break;
        }

        return $value;
    }

    protected function applySearch(Builder $query, string $search): Builder
    {
        if (!$this->searchWhereColumns && !$this->searchHavingColumns) {
            return $query;
        }
        $query->where(function ($query) use ($search) {
            /* @var Builder $query */
            foreach ($this->searchWhereColumns as $column) {
                $query->orWhere(DB::raw($column), 'like', '%' . $search . '%');
            }
        });
        foreach ($this->searchHavingColumns as $column) {
            $query->orHaving($column, 'like', '%' . $search . '%');
        }

        return $query;
    }

    protected function getFilterErrorMessage(): string
    {
        return __('exception.invalid_filter', [
            'params' => implode(', ', array_keys(array_merge($this->filterWhereColumns, $this->filterHavingColumns))),
        ]);
    }

    public function last($limit = 0, array $relationships = [])
    {
        $query = $this->model->newQuery();
        if ($relationships) {
            $query->with($relationships);
        }
        $query->latest();
        $query->limit($limit);
        return $query->get();
    }
    //	public function butchCreate(array $data)
    //	{
    //		if(sizeof($data) > 3000){
    //			for($i=0;$i<sizeof($data);$i++){
    //				try {
    //					$this->model->insert($data[$i]);
    //				}catch (\Exception $e){
    ////					dd($data[$i]);
    //				}
    //			}
    //		}else {
    //			$this->model->insert($data);
    //		}
    ////		$data = [
    ////			['user_id'=>'Coder 1', 'subject_id'=> 4096],
    ////			['user_id'=>'Coder 2', 'subject_id'=> 2048],
    ////			//...
    ////		];
    ////
    ////		Model::insert($data); // Eloquent approach
    ////		DB::table('table')->insert($data); // Query Builder approach
    ////
    ////		return $this->model->whereIn('id', $ids)->update($data);
    //		return true;
    //	}
}
