<?php

namespace App\Repositories;

use App\Models\Promotion;

class PromotionRepository 
{
    private $promotion;

    public function __construct(Promotion $promotion)
    {
        $this->promotion = $promotion;
    }

    public function findByIdModel(string $id_model)
    {
        return $this->promotion->where('id_modelo', $id_model)->first();
    }

    public function update(Promotion $promotion, array $data): void
    {
        $promotion->update($data);
    }

    public function store(array $data)
    {
        $this->promotion->firstOrCreate($data);
    }
    
}
