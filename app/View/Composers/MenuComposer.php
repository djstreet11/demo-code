<?php

namespace App\View\Composers;

use App\Repositories\CategoryRepository;
use App\Repositories\PageRepository;
use App\Repositories\TagRepository;
use Illuminate\View\View;

class MenuComposer
{
    private CategoryRepository $categoryRepository;
    private TagRepository $tagRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository      = $tagRepository;
    }

    public function compose(View $view)
    {
        $categories = $this->categoryRepository->allRoot();
        $tags       = $this->tagRepository->all();

        $view->with('menu_categories', $categories);
        $view->with('menu_tags', $tags);
    }
}
