<?php

namespace App\Http\Livewire\Dashboard\Settings;

use App\Models\Settings;
use Livewire\Component;
use Livewire\WithFileUploads;

class WarrantyAndExchangePage extends Component
{
    use WithFileUploads;

    public Settings $model;
    public $setting_value;

    public function mount()
    {
        $this->setting_value = $this->model->value;
    }

    public function render()
    {
        return view('livewire.dashboard.settings.warranty_and_exchange_page');
    }

    public function savePageInfo()
    {
        $this->model->value = $this->setting_value;
        $this->model->save();
        $this->dispatchBrowserEvent('alert',
            ['type' => 'success', 'message' => 'page info saved Successfully!']);
    }
}
