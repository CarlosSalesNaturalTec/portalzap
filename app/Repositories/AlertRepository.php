<?php

namespace App\Repositories;

use App\Models\Alert;

class AlertRepository 
{
    private $alert;

    public function __construct(Alert $alert)
    {
        $this->alert = $alert;
    }

    public function store(array $data)
    {
        $this->alert->firstOrCreate($data);
    }
    
}
