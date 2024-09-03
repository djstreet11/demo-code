<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Repositories\AttributeRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ShippingRepository;
use App\Repositories\StoreRepository;
use App\Repositories\TagRepository;

class ProductController extends Controller
{
	private ProductRepository $productRepository;
	private AttributeRepository $attributeRepository;
	private ShippingRepository $shippingRepository;
	private PaymentRepository $paymentRepository;
	private StoreRepository $storeRepository;
	private TagRepository $tagRepository;
	private CategoryRepository $categoryRepository;

    public function __construct(
        ProductRepository $productRepository,
        AttributeRepository $attributeRepository,
        ShippingRepository $shippingRepository,
        PaymentRepository $paymentRepository,
        StoreRepository $storeRepository,
		TagRepository $tagRepository,
		CategoryRepository $categoryRepository,
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->shippingRepository = $shippingRepository;
        $this->paymentRepository = $paymentRepository;
        $this->storeRepository = $storeRepository;
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function index(Request $request, $category_slug, $product_slug): Factory|View|Application
    {
        $product = $this->productRepository->findBySlug('slug', $product_slug, ['variations']);

		$this->analyticsService->visit_products($product);

        $attributes = $this->attributeRepository->findParamsByProductId($product->id);
		$params = $request->all();
        $shippings = $this->shippingRepository->list($params);
        $payments = $this->paymentRepository->list($params);
        $stores = $this->storeRepository->list($params);


        return view('front.pages.product', [
            'product'    => $product,
            'attributes' => $attributes,
			'shippings'     => $shippings->sortBy('position'),
            'payments' => $payments,
            'stores' => $stores,
			'breadcrumbs'     => $this->generate_breadcrumbs($product),
        ]);
    }
	public function generate_breadcrumbs($product)
	{
		/* @var $product Product */
		$categories = $product->categories;
		$main = $product->main_category;

		if ($main){
			for ($i=0;$i<sizeof($categories);$i++){
				if ($main == $categories[$i]->id){
					$use_category_id = $categories[$i]->id;
				}
			}
		}else{
			$use_category_id = $categories[0]->id;
		}

		return $this->categoryRepository->breadcrumbs($use_category_id);
	}
}
