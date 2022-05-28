<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComprasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('compras', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('pedido');
        //     $table->string('usuario');
        //     $table->double('unidades', 16, 2)->default(0);
        //     $table->double('total', 16, 2)->default(0);
        //     $table->enum('tipo_pago', ['Tarjeta Crédito/Debito', 'OXXO', 'Transferencia Bancaria']);
        //     $table->boolean('entregado')->default(false);
        //     $table->text('entregado_por')->nullable();
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
        Schema::dropIfExists('compras');
    }
}
