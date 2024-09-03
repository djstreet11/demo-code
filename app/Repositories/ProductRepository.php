<?php

namespace App\Repositories;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $product)
    {
        $this->model = $product;
    }



    public function search($search): Builder
    {
        return $this->applySearch(query: $this->model->newQuery(), search: $search);
    }

    public function listByFilterCategory($params, $relationships = [])
    {
		DB::select("set session sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $category_ids = [];
        if (! empty($params['category_id'])) {


			$list =  Cache::remember('get_recursive_cte_cat_id_'.$params['category_id'], 1200, function () use ($params) {
				return DB::select('
				with recursive cte (id)
				as (
				  select     id
				  from       categories
				  where      id = ?
				  union all
				  select     p.id
				  from       categories p
				  inner join cte
						  on p.parent_id = cte.id
				)
				select * from cte;', [$params['category_id']]);
			});



            if (! empty($list)) {
                $category_ids = array_column($list, 'id');
            }
        }

        $query = $this->model->newQuery()
            ->select(
				'products.*'
			);
		$query->whereIn('products.active',['1','on']);
        if (! empty($category_ids)) {
            $query->join('category_product', 'category_product.product_id', '=', 'products.id');
            $query->whereIn('category_product.category_id', $category_ids);
        }
        if (! empty($params['attr'])) {
			foreach ($params['attr'] as $key => $value) {
				for ($i = 0; $i < count($value); $i++) {
					$query->join('product_attribute_param_values as pr_attr_v' . $key . $i, function ($join) use ($value, $key, $i) {
						$join->on('pr_attr_v' . $key . $i . '.product_id', '=', 'products.id');
						$join->where('pr_attr_v' . $key . $i . '.attribute_id', '=', $key);
						$join->where('pr_attr_v' . $key . $i . '.attribute_param_id', '=', $value[$i]);
					});
				}
			}
		}
		$query->groupBy('products.id');

		$query->addSelect(
			DB::raw("(
				CASE
				when `products`.`type` = 'standard' then `products`.`price`
				when `products`.`type` = 'variable' then (SELECT AVG(`variations`.`price`) as `price`  from `variations` where `variations`.`product_id` = `products`.`id` )
				ELSE 0 END
				) AS `pr_price`"));

		if (! empty($params['minPrice'])) {
			$query->whereRaw(
				DB::raw("
				   CASE
						WHEN `products`.`type` = 'standard' THEN `products`.`price` >= {$params['minPrice']}
						WHEN `products`.`type` = 'variable' THEN (SELECT AVG(`variations`.`price`) as `price`  from `variations` where `variations`.`product_id` = `products`.`id` )  >= {$params['minPrice']}
						 END
				")
			);
		}
		if (! empty($params['maxPrice'])) {
			$query->whereRaw(
				DB::raw("
				   CASE
						WHEN `products`.`type` = 'standard' THEN `products`.`price` <= {$params['maxPrice']}
						WHEN `products`.`type` = 'variable' THEN (SELECT AVG(`variations`.`price`) as `price`  from `variations` where `variations`.`product_id` = `products`.`id` )  <= {$params['maxPrice']}
						 END
				")
			);
		}
		if (! empty($params['sort'])) {

			if ($params['sort'] == 'price_low'){
				$query->orderBy('pr_price');
			}
		} else {
			$query->orderByDesc('popularity');
		}

        return $this->processList($query, $params, $relationships);
    }

    public function listByFilterTag($params, $relationships = [])
    {
		DB::select("set session sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $query = $this->model->newQuery()
            ->select('products.*');

		$query->whereIn('products.active',['1','on']);
        if (! empty($params['tag_id'])) {
            $query->join('product_tag', 'product_tag.product_id', '=', 'products.id');
            $query->where('product_tag.tag_id', $params['tag_id']);
        }
		if (! empty($params['attr'])) {
			foreach ($params['attr'] as $key => $value) {
				for ($i = 0; $i < count($value); $i++) {
					$query->join('product_attribute_param_values as pr_attr_v' . $key . $i, function ($join) use ($value, $key, $i) {
						$join->on('pr_attr_v' . $key . $i . '.product_id', '=', 'products.id');
						$join->where('pr_attr_v' . $key . $i . '.attribute_id', '=', $key);
						$join->where('pr_attr_v' . $key . $i . '.attribute_param_id', '=', $value[$i]);
					});
				}
			}
		}
		$query->groupBy('products.id');
		$query->addSelect(
			DB::raw("(
				CASE
				when `products`.`type` = 'standard' then `products`.`price`
				when `products`.`type` = 'variable' then (SELECT AVG(`variations`.`price`) as `price`  from `variations` where `variations`.`product_id` = `products`.`id` )
				ELSE 0 END
				) AS `pr_price`"));

		if (! empty($params['minPrice'])) {
			$query->whereRaw(
				DB::raw("
				   CASE
						WHEN `products`.`type` = 'standard' THEN `products`.`price` >= {$params['minPrice']}
						WHEN `products`.`type` = 'variable' THEN (SELECT AVG(`variations`.`price`) as `price`  from `variations` where `variations`.`product_id` = `products`.`id` )  >= {$params['minPrice']}
						 END
				")
			);
		}
		if (! empty($params['maxPrice'])) {
			$query->whereRaw(
				DB::raw("
				   CASE
						WHEN `products`.`type` = 'standard' THEN `products`.`price` <= {$params['maxPrice']}
						WHEN `products`.`type` = 'variable' THEN (SELECT AVG(`variations`.`price`) as `price`  from `variations` where `variations`.`product_id` = `products`.`id` )  <= {$params['maxPrice']}
						 END
				")
			);
		}
		if (! empty($params['sort'])) {
			if ($params['sort'] == 'price_low'){
				$query->orderBy('pr_price');
			}
			if ($params['sort'] == 'price_high'){
				$query->orderByDesc('pr_price');
			}
			if ($params['sort'] == 'tag_new'){ //TODO from tag_new to new
				$query->orderByDesc('created_at');
			}
			if ($params['sort'] == 'tag_hit'){ //TODO from tag_hit to hit
				$query->addSelect(
					DB::raw("(SELECT SUM(`order_items`.`qty`) as `sell_count`  from `order_items` where `order_items`.`product_id` = `products`.`id` ) AS `sell_count`"));
				$query->orderByDesc('sell_count');
			}
			if ($params['sort'] == 'popularity'){ //TODO from tag_hit to hit
				$query->addSelect(
					DB::raw("(SELECT `analytics`.`visits`  from `analytics` where `analytics`.`model_id` = `products`.`id` and `analytics`.`model` = 'product') AS `sell_count`"));
				$query->orderByDesc('sell_count');
			}
		}

        return $this->processList($query, $params, $relationships);
    }

    public function listByTagSlug($slug, $limit = 0, $random = false)
    {
        $query = $this->model->newQuery()->select('products.*');

		$query->whereIn('products.active',['1','on']);
		if($random){
			$query->inRandomOrder();
		}
        $query->join('product_tag as pt', 'pt.product_id', '=', 'products.id');
        $query->join('tags', 'pt.tag_id', '=', 'tags.id');
        $query->where(['tags.slug' => $slug]);
        if ($limit > 0) {
            $query->limit($limit);
        }

        return $this->processList($query, ['cache_key' => "listByTagSlug_{$slug}_{$limit}"]);
    }
    public function listByTagId($id, $limit = 0, $random = false)
    {
        $query = $this->model->newQuery()->select('products.*');

		$query->whereIn('products.active',['1','on']);
		if($random){
			$query->inRandomOrder();
		}
        $query->join('product_tag as pt', 'pt.product_id', '=', 'products.id');
        $query->join('tags', 'pt.tag_id', '=', 'tags.id');
        $query->where(['tags.id' => $id]);
        if ($limit > 0) {
            $query->limit($limit);
        }

        return $this->processList($query, ['cache_key' => "listByTagId_{$id}_{$limit}"]);
    }


	public function listByFilterCategory_IDS($params, $relationships = [])
	{
		DB::select("set session sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
		$category_ids = [];
		if (! empty($params['category_id'])) {


			$list =  Cache::remember('get_recursive_cte_cat_id_'.$params['category_id'], 1200, function () use ($params) {
				return DB::select('
				with recursive cte (id)
				as (
				  select     id
				  from       categories
				  where      id = ?
				  union all
				  select     p.id
				  from       categories p
				  inner join cte
						  on p.parent_id = cte.id
				)
				select * from cte;', [$params['category_id']]);
			});



			if (! empty($list)) {
				$category_ids = array_column($list, 'id');
			}
		}

		$query = $this->model->newQuery()
			->select(
				'products.*'
			);

		$query->whereIn('products.active',['1','on']);
		if (! empty($category_ids)) {
			$query->join('category_product', 'category_product.product_id', '=', 'products.id');
			$query->whereIn('category_product.category_id', $category_ids);
		}
		if (! empty($params['attr'])) {
			foreach ($params['attr'] as $key => $value) {
				for ($i = 0; $i < count($value); $i++) {
					$query->join('product_attribute_param_values as pr_attr_v' . $key . $i, function ($join) use ($value, $key, $i) {
						$join->on('pr_attr_v' . $key . $i . '.product_id', '=', 'products.id');
						$join->where('pr_attr_v' . $key . $i . '.attribute_id', '=', $key);
						$join->where('pr_attr_v' . $key . $i . '.attribute_param_id', '=', $value[$i]);
					});
				}
			}
		}
		$query->groupBy('products.id');

		$query->addSelect(
			DB::raw("(
				CASE
				when `products`.`type` = 'standard' then `products`.`price`
				when `products`.`type` = 'variable' then (SELECT AVG(`variations`.`price`) as `price`  from `variations` where `variations`.`product_id` = `products`.`id` )
				ELSE 0 END
				) AS `pr_price`"));

		if (! empty($params['minPrice'])) {
			$query->whereRaw(
				DB::raw("
				   CASE
						WHEN `products`.`type` = 'standard' THEN `products`.`price` >= {$params['minPrice']}
						WHEN `products`.`type` = 'variable' THEN (SELECT AVG(`variations`.`price`) as `price`  from `variations` where `variations`.`product_id` = `products`.`id` )  >= {$params['minPrice']}
						 END
				")
			);
		}
		if (! empty($params['maxPrice'])) {
			$query->whereRaw(
				DB::raw("
				   CASE
						WHEN `products`.`type` = 'standard' THEN `products`.`price` <= {$params['maxPrice']}
						WHEN `products`.`type` = 'variable' THEN (SELECT AVG(`variations`.`price`) as `price`  from `variations` where `variations`.`product_id` = `products`.`id` )  <= {$params['maxPrice']}
						 END
				")
			);
		}
		if (! empty($params['sort'])) {
			if ($params['sort'] == 'price_low'){
				$query->orderBy('pr_price');
			}
			if ($params['sort'] == 'price_high'){
				$query->orderByDesc('pr_price');
			}
			if ($params['sort'] == 'tag_new'){ //TODO from tag_new to new
				$query->orderByDesc('created_at');
			}
			if ($params['sort'] == 'tag_hit'){ //TODO from tag_hit to hit
				$query->addSelect(
					DB::raw("(SELECT SUM(`order_items`.`qty`) as `sell_count`  from `order_items` where `order_items`.`product_id` = `products`.`id` ) AS `sell_count`"));
				$query->orderByDesc('sell_count');
			}
			if ($params['sort'] == 'popularity'){ //TODO from tag_hit to hit
				$query->addSelect(
					DB::raw("(SELECT `analytics`.`visits`  from `analytics` where `analytics`.`model_id` = `products`.`id` and `analytics`.`model` = 'product') AS `sell_count`"));
				$query->orderByDesc('sell_count');
			}
		}

		if ($relationships) {
			$query->with($relationships);
		}

		$query = $this->applyFilters($query, $params);

		if (! empty($params['search'])) {
			$query = $this->applySearch($query, $params['search']);
		}

		return array_unique($query->get()->modelKeys());
//		return $query->without(['categories','tags','media'])->get()->toArray();
	}
	public function max_price_of_ids($ids_list)
	{
		if ($ids_list) {
			$res = DB::select("SELECT `variations`.`max_price_v`, `products`.`max_price_s`
		FROM (
			SELECT MAX(`variations`.`price`) as 'max_price_v'
			FROM `variations`
			WHERE `variations`.`product_id` IN (
				SELECT `products`.`id` as 'id'
				from `products`
				WHERE `products`.`type` = 'variable' and `products`.`id` IN (" . implode(',', $ids_list) . "))
			) `variations`
		JOIN
			(
			SELECT MAX(`products`.`price`) as 'max_price_s'
			FROM `products` where `products`.`type` = 'standard' and  `products`.`id` IN (" . implode(',', $ids_list) . ")
			) `products`");
			if (isset($res[0]->max_price_v) && isset($res[0]->max_price_s)){
				if ($res[0]->max_price_v > $res[0]->max_price_s){
					return $res[0]->max_price_v;
				}else{
					return $res[0]->max_price_s;
				}
			}elseif (!isset($res[0]->max_price_v)){
				return $res[0]->max_price_s;
			}elseif (!isset($res[0]->max_price_s)){
				return $res[0]->max_price_v;
			}
		}
		return 0;
	}
	public function min_price_of_ids($ids_list)
	{
		if ($ids_list) {
		$res = DB::select("SELECT `variations`.`min_price_v`, `products`.`min_price_s`
		FROM (
			SELECT MIN(`variations`.`price`) as 'min_price_v'
			FROM `variations`
			WHERE `variations`.`product_id` IN (
				SELECT `products`.`id` as 'id'
				from `products`
				WHERE `products`.`type` = 'variable' and `products`.`id` IN (".implode(',',$ids_list)."))
			) `variations`
		JOIN
			(
			SELECT MIN(`products`.`price`) as 'min_price_s'
			FROM `products` where `products`.`type` = 'standard' and  `products`.`id` IN (".implode(',',$ids_list).")
			) `products`");
			if (isset($res[0]->min_price_v) && isset($res[0]->min_price_s)){
				if ($res[0]->min_price_v > $res[0]->min_price_s){
					return $res[0]->min_price_s;
				}else{
					return $res[0]->min_price_v;
				}
			}elseif (!isset($res[0]->min_price_v)){
				return $res[0]->min_price_s;
			}elseif (!isset($res[0]->min_price_s)){
				return $res[0]->min_price_v;
			}
		}
		return 0;
	}

	public function findBySlug(string $column, string $value, array $relationships = [])
	{
		return $this->model
			->with($relationships)
			->where($column, $value)
			->firstOrFail();
	}

	public function count(){

	}
	//	protected $filterWhereColumns = [
    //		'email' => 'email',
    //		'user' => 'user',
    //	];
    //
    //	protected $searchWhereColumns = [
    //		'email' => 'email',
    //		'user' => 'user',
    //	];

    //	protected $filterWhereColumns = [
    //		'createdBy' => 'createdBy',
    //		'releaseName' => 'releaseName',
    //		'artists' => 'artists',
    //		'primaryGenre' => 'primaryGenre',
    //		'label' => 'label',
    //		'upc' => 'upc',
    //		'track' => 'track',
    //		'albumFormat' => 'albumFormat',
    //	];
    //
    //	protected $searchWhereColumns = [
    //		'createdBy' => 'createdBy',
    //		'releaseName' => 'releaseName',
    //		'artists' => 'artists',
    //		'primaryGenre' => 'primaryGenre',
    //		'label' => 'label',
    //		'upc' => 'upc',
    //		'track' => 'track',
    //		'albumFormat' => 'albumFormat',
    //	];

    //	public function create(array $data): Model
    //	{
    //		$model = $this->model->newInstance();
    //		$model->fill($data);
    //
    //		if(isset($data['img'])) {
    //			$model->addMedia($data['img'])->toMediaCollection('img');
    //		}
    //
    //		try {
    //			$model->save();
    //		} catch (\Exception $e) {
    //			throw new \Exception($e->getMessage());
    //		}
    //
    //
    ////		$model->created_by = auth()->user()->id;
    //
    //
    //
    //
    //
    ////		$product->code = $data['code'] ?? $this->generateCode('', 0, 12);
    ////		$product->supplier_id = $data['supplier_id'] ?? AuthHelper::supplierId();
    ////		$product->category_id = $data['category_id'];
    ////		$product->model_id = $data['model_id'];
    ////		$product->brand_id = $data['brand_id'];
    ////		$product->image = $data['image'] ?? null;
    ////		$product->color = $data['color'];
    ////		ProductColor::updateOrCreate([
    ////			'name' => $product->color,
    ////		]);
    ////		$product->size = $data['size'];
    ////		ProductSize::updateOrCreate([
    ////			'name' => $product->size,
    ////		]);
    ////
    ////		if (!$product->save()) {
    ////			return false;
    ////		}
    ////
    ////		if (!empty($data['attributes'])) {
    ////			$attributes = $product->attributes()->pluck('slug', 'id');
    ////
    ////			foreach ($data['attributes'] as $attribute) {
    ////				if (empty($attribute['slug'])) {
    ////					$attribute['slug'] = $attributes[$attribute['id']];
    ////				} elseif (empty($attribute['id'])) {
    ////					$attribute['id'] = $attributes->search($attribute['slug']);
    ////				}
    ////				$product->{$attribute['slug']} = $attribute['value'];
    ////			}
    ////			$product->save();
    ////		}
    //
    //
    //
    //		return $model;
    //	}

    //    public function featured(): LengthAwarePaginator
    //    {
    //        $params = ['featured' => true, 'per_page' => 999];
    //        $query = $this->model->newQuery();
    //        $query->where(['featured' => true]);
    //
    //        return $this->processList($query, $params, []);
    //    }

    //	public function list(array $params = [], array $relationships = []): LengthAwarePaginator
    //	{
    //		$query = $this->newQuery();
    //		$query->select([
    //			'payment_status.*',
    //		]);
    //
    //		if(!auth()->user()->hasRole('admin')) {
    //			$userEmail = auth()->user()->email;
    //			$query->where('email', $userEmail);
    //		}
    //
    //		return $this->processList($query, $params, $relationships);
    //	}

    //	public function list_created_by(array $params = [], array $relationships = []): LengthAwarePaginator
    //	{
    //		$query = $this->newQuery();
    //		$query->select([
    //			'artists.id',
    //			'artists.name',
    //		])->where('created_by', auth()->user()->id);
    //		return $this->processList($query, $params, $relationships);
    //	}

    //	public function findByCategory(array $params = [], array $relationships = []): LengthAwarePaginator
    //	{
    //		$query = $this->newQuery();
    //		$query->select(['recipes.*']);
    //		$query->where('recipes.category_id','=',$params['category_id']);
    ////		$relationships[] = 'category';
    ////		$params['order_by'] = 'position';
    ////		$params['order'] = 'asc';
    //
    //		return $this->processList($query, $params, $relationships);
    //	}
    //	public function listByGroupId(array $params = [], array $relationships = []): LengthAwarePaginator
    //	{
    //		$query = $this->newQuery();
    //		$query->select([
    //						   'times.id',
    //						   'times.worker_id',
    //						   'times.group_id',
    //						   'times.position',
    //						   'times.d1_from',
    //						   'times.d1_to',
    //						   'times.d2_from',
    //						   'times.d2_to',
    //						   'times.d3_from',
    //						   'times.d3_to',
    //						   'times.d4_from',
    //						   'times.d4_to',
    //						   'times.d5_from',
    //						   'times.d5_to',
    //						   'times.d6_from',
    //						   'times.d6_to',
    //						   'times.d7_from',
    //						   'times.d7_to',
    //						   'times.description',
    //					   ]);
    //		$query->where('times.group_id','=',$params['group_id']);
    //		$relationships[] = 'category';
    ////		$relationships[] = 'difficulty';
    ////		$params['order_by'] = 'position';
    ////		$params['order'] = 'asc';
    //		return $this->processList($query, $params, $relationships);
    //	}

}
