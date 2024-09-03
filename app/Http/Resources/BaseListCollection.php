<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * BaseCollection
 *
 * @mixin LengthAwarePaginator
 */
abstract class BaseListCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->data(),
            'meta' => [
                'current_page' => $this->currentPage(),
                'from'         => $this->firstItem(),
                'to'           => $this->lastItem(),
                'per_page'     => $this->perPage(),
                'total_pages'  => $this->lastPage(),
                'total'        => $this->total(),
            ],
        ];
    }

    /**
     * Specifies data item in response
     */
    protected function data(): Collection
    {
        return $this->collection;
    }
}
