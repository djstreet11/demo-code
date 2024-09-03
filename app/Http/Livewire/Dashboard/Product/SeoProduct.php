<?php

namespace App\Http\Livewire\Dashboard\Product;

use App\Models\Product;
use App\Repositories\AttributeRepository;
use App\Services\SeoService;
use Livewire\Component;
use Livewire\WithFileUploads;

class SeoProduct extends Component
{
    use WithFileUploads;

    public Product $model;
    public $title;
    public $title2 = '';
    public $title_len = 0;
    public $description;
    public $description2 = '';
    public $description_len = 0;
    private AttributeRepository $attributeRepository;

    public function mount(
        AttributeRepository $attributeRepository,
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->title = $this->model->seo_title_template;
        $this->title2 = $this->model->seo_title;
        $this->title_len = strlen($this->model->seo_title);
        $this->description = $this->model->seo_description_template;
        $this->description2 = $this->model->seo_description;
        $this->description_len = strlen($this->model->seo_description);
    }

    public function updatedTitle($title)
    {
        $attributeRepository = new AttributeRepository(new \App\Models\Attribute());
        $seoService = new SeoService($attributeRepository, $this->model);
        $this->title2 = $seoService->test_seo_fields('product', 'title', $title);
    }

    public function updatedDescription($description)
    {
        $attributeRepository = new AttributeRepository(new \App\Models\Attribute());
        $seoService = new SeoService($attributeRepository, $this->model);
        $this->description2 = $seoService->test_seo_fields('product', 'description', $description);
    }

    public function render(
        AttributeRepository $attributeRepository,
    ) {
        $this->attributeRepository = $attributeRepository;
        $attr = $this->attributeRepository->all();

        return view('livewire.dashboard.product.seo', [
            'attributes' => $attr,
        ]);
    }

    public function savePageInfo(
        AttributeRepository $attributeRepository,
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->model->seo_title_template = $this->title;
        $this->model->seo_description_template = $this->description;
        $this->model->save();
        $seoService = new SeoService($this->attributeRepository, $this->model);
        $seoService->apply_seo_fields('product');

        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Зміни збережені успішно!']);
    }
}
