<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Traits\CRUDController;
use App\Repositories\FaqRepository;
use App\Services\FaqService;

class FaqController extends Controller
{
    use CRUDController;

    private $viewFolder = 'faq';
    private $singleName = 'faq';
    private $arrName = 'faqs';
    private $ListCollection = 'App\Http\Resources\ListCollections\FaqListCollection';
    private $Resource = 'App\Http\Resources\FaqResource';
    private $rout_all = 'dashboard.faq.all';

    /** @var FaqService */
    private $service;
    /** @var FaqRepository */
    private $repository;

    public function __construct(
        FaqService $service,
        FaqRepository $repository,
    ) {
        $this->service = $service;
        $this->repository = $repository;
    }
}
