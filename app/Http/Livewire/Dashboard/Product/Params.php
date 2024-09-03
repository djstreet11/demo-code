<?php

namespace App\Http\Livewire\Dashboard\Product;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeParamValue;
use Livewire\Component;
use Livewire\WithFileUploads;

class Params extends Component
{
    use WithFileUploads;

    public Product $model;
    public array|object $attributes = [];
    public array $selected_attr = [];
    public $attr;

    public function mount()
    {
        $this->attributes = Attribute::select()->where('type','=','param')->with(['values'])->get();
        $tmp = ProductAttributeParamValue::select()->where('product_id',$this->model->id)
			->join('attribute_param_values','product_attribute_param_values.attribute_param_id','=','attribute_param_values.id')->get();
        for ($i = 0; $i < sizeof($tmp); $i++) {
            $this->selected_attr[$tmp[$i]->attribute_id] = $tmp[$i]->attribute_param_id;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.product.params');
    }

    public function submit()
    {
		ProductAttributeParamValue::where('product_id', '=', $this->model->id)->delete();
		foreach ($this->selected_attr as $id => $item){
			if (!empty($item)) {
				$model = new ProductAttributeParamValue([
					'product_id' => $this->model->id,
					'attribute_id' => $id,
					'attribute_param_id' => $item,
				]);
				$model->save();
			}
		}
		$this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Зміни збережені успішно!']);
    }
}
