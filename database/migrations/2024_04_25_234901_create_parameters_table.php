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
        Schema::create('parameters', function (Blueprint $table) {
            $table->id();
            $table->string("id_telefone", 20)->nullable(true);
            $table->string("id_waba", 20)->nullable(true);
            $table->string("id_aplicativo", 20)->nullable(true);
            $table->string("api_url", 80)->nullable(true);
            $table->string("token", 512)->nullable(true);
            $table->string("url_prompt", 256)->nullable(true);
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
        Schema::dropIfExists('parameters');
    }
};
