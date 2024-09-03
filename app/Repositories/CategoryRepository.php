<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $category)
    {
        $this->model = $category;
    }
    public function breadcrumbs($id): array
    {
        $db_res = Cache::remember('breadcrumbs_'.$id, 1200, function () use ($id) {
            return DB::select('
				with recursive cte (id, title, parent_id, slug)
				as (
				  select     id,
							 title,
							 parent_id,
							 slug
				  from       categories
				  where      id = ?
				  union all
				  select     p.id,
							 p.title,
							 p.parent_id,
							 p.slug
				  from       categories p
				  inner join cte
						  on p.id = cte.parent_id
				)
				select * from cte;', [$id]);
        });

        return array_reverse($db_res);
    }
}
