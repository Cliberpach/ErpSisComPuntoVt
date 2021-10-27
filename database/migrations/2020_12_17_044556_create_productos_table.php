<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->Increments('id');
            $table->unsignedInteger('categoria_id');
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');
            $table->unsignedInteger('marca_id');
            $table->foreign('marca_id')->references('id')->on('marcas')->onDelete('cascade');
            $table->unsignedInteger('almacen_id');
            $table->foreign('almacen_id')->references('id')->on('almacenes')->onDelete('cascade');
            $table->string('codigo', 50)->nullable();
            $table->string('nombre');
            $table->mediumText('descripcion')->nullable();
            $table->string('medida');
            $table->string('codigo_barra')->nullable();
            // $table->string('moneda');
            $table->unsignedDecimal('stock', 15, 2)->default(0);
            $table->unsignedDecimal('stock_minimo', 15, 2);
            $table->unsignedDecimal('precio_compra', 15, 2)->nullable();
            $table->unsignedDecimal('precio_venta_minimo', 15, 2);
            $table->unsignedDecimal('precio_venta_maximo', 15, 2);
            $table->unsignedDecimal('peso_producto', 15, 2)->default(0);
            $table->boolean('igv');
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
        Schema::dropIfExists('productos');
    }
}
