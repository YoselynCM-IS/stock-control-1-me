<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('pedidos', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('compra_id')->nullable();
        //     $table->foreign('compra_id')->references('id')->on('compras');
        //     $table->unsignedBigInteger('libro_id')->nullable();
        //     $table->foreign('libro_id')->references('id')->on('libros');
        //     $table->float('costo_unitario', 8, 2);
        //     $table->integer('unidades')->default(0);
        //     $table->double('total', 16, 2)->default(0);
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
}
