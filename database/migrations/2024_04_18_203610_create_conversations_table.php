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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();            
            $table->integer('resp'); // 0 = chatBot  1 = Contato   2 = Atendente
            $table->foreignId('id_contato')->constrained()->on('contacts');
            $table->foreignId('id_promo')->nullable(true)->constrained()->on('promotions');
            $table->text('mensagem');
            $table->string('id_message',100);
            $table->integer('time_sent')->default(0);
            $table->integer('time_delivered')->default(0);
            $table->integer('time_read')->default(0);
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
        Schema::dropIfExists('conversations');
    }
};
