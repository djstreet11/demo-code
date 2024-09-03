<?php

namespace App\Repositories;

use App\Models\Faq;

class FaqRepository extends BaseRepository
{
    public function __construct(Faq $Faq)
    {
        $this->model = $Faq;
    }
}
