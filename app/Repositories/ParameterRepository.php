<?php

namespace App\Repositories;

use App\Models\Parameter;

class ParameterRepository 
{
    private $parameter;

    public function __construct(Parameter $parameter)
    {
        $this->parameter = $parameter;
    }

    public function findById(int $id)
    {
        return $this->parameter->find($id);
    }
    
}
