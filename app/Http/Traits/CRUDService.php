<?php

namespace App\Http\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;

trait CRUDService
{
    /**
     * @throws Exception
     *
     * @return Model|false
     */
    public function create(array $data)
    {
        $response = $this->repository->create($data);

        return $response
            ? $response->fresh()
            : false;
    }

    /**
     * @return Model|false
     */
    public function update(Model $model, array $data)
    {
        $response = $this->repository->update($model->id, $data);

        return $response
            ? $model->fresh()
            : false;
    }
}
