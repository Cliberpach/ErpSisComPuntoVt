<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotizacionDetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cotizacion_detalles', function (Blueprint $table) {
            $table->Increments('id');
            $table->unsignedInteger('cotizacion_id');
            $table->foreign('cotizacion_id')->references('id')->on('cotizaciones')->onDelete('cascade');
            $table->unsignedInteger('producto_id');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->unsignedInteger('cantidad');
            $table->unsignedDecimal('precio_inicial', 15, 2);
            $table->unsignedDecimal('precio_unitario', 15, 2);
            $table->unsignedDecimal('descuento', 15, 2)->default(0.00);
            $table->unsignedDecimal('dinero', 15, 2)->default(0.00);
            $table->unsignedDecimal('precio_nuevo', 15, 2);
            $table->unsignedDecimal('valor_unitario', 15, 2);
            $table->unsignedDecimal('valor_venta', 15, 2);  
            $table->enum('estado',['ACTIVO','ANULADO'])->default('ACTIVO');
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
        Schema::dropIfExists('cotizacion_detalles');
    }
}
