<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * Class DefaultListRequest
 */
class SearchRequest extends APIRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status_id' => 'string|nullable',
            'order_by'  => 'string|nullable',
            'order'     => ['string', Rule::in(['asc', 'desc'])],
            'per_page'  => 'integer|nullable',
            'page'      => 'integer|nullable',
            'attr'      => 'array|nullable',
            'search'    => 'string|nullable',
			'sort'      => 'string|nullable',
			'minPrice'      => 'integer|nullable',
			'maxPrice'      => 'integer|nullable',
        ];
    }
}
