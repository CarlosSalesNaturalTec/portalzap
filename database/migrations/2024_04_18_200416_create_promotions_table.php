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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string("promo",100);
            $table->string('language',6)->default('pt_BR');
            $table->dateTime('data_promo');
            $table->string('tipo_cabecalho',12)->nullable(true);
            $table->string('corpo_cabecalho',256)->nullable(true);
            $table->string('tipo_rodape',12)->nullable(true);
            $table->string('corpo_rodape', 60)->nullable(true);
            $table->string('tipo_botao1',25)->nullable(true);
            $table->string('corpo_botao1',25)->nullable(true);
            $table->string('tipo_botao2',25)->nullable(true);
            $table->string('corpo_botao2',25)->nullable(true);
            $table->string('mensagem',1024);
            $table->integer('quant_envios')->default(0);
            $table->string('status', 1024)->nullable(true);
            $table->string('categoria',20)->nullable(true);
            $table->string('id_modelo',30)->nullable(true);
            $table->string('obs',256)->nullable(true);
            $table->string('score_qualidade_atual',20)->nullable(true);
            $table->string('score_qualidade_anterior',20)->nullable(true);
            $table->integer('atualizacao_timestamp')->default(0);
            $table->string('status_contato',10)->nullable(true);
            $table->integer('quant_cancelamentos')->default(0);
            $table->integer('quant_aceites')->default(0);
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
        Schema::dropIfExists('promotions');
    }
};
