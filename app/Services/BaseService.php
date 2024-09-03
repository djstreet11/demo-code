<?php

namespace App\Services;

use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService
{
    /** @var BaseRepository */
    protected $repository;

    public function create(array $data): bool|Model
    {
        $response = $this->repository->create($data);

        return $response ? $response->fresh() : false;
    }

    public function all()
    {
        $response = $this->repository->all();

        return $response ? $response->fresh() : false;
    }

    public function update(Model $model, array $data): bool|Model
    {
        $response = $this->repository->update($model->id, $data);

        return $response ? $model->fresh() : false;
    }

    public function delete(Model $model): bool|Model
    {
        $response = $this->repository->delete($model->id);

        return $response ? $response : false;
    }
}
