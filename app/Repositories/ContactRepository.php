<?php

namespace App\Repositories;

use App\Models\Contact;

class ContactRepository 
{
    private $contact;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    public function findByTel(string $tel)
    {
        return $this->contact->where('telefone', $tel)->first();
    }  
    
    public function store(array $data)
    {
        return $this->contact->firstOrCreate($data)->id;
    }

    public function update(Contact $contact, array $data): void
    {
        $contact->update($data);
    }
    
}
