<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string("nome", 256);
            $table->string("telefone", 20);
            $table->string("endereco", 256)->nullable(true);
            $table->string("sexo", 20)->nullable(true);
            $table->date("nascimento")->nullable(true);
            $table->dateTime("dataCadastro");
            $table->dateTime("ultimo_contato")->nullable(true);
            
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contacts');
    }
};
