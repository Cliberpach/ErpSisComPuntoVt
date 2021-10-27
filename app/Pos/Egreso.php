<?php

namespace App\Pos;

use App\Mantenimiento\Tabla\Detalle;
use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    protected $table="egreso";
    protected $fillable=[
        'tipodocumento_id',
        'cuenta_id',
        'documento',
        'descripcion',
        'importe',
        'estado'
    ];
    public $timestamps=true;
    public function cuenta() {
        return $this->belongsTo(Detalle::class,'cuenta_id');
    }
    public function tipoDocumento() {
        return $this->belongsTo(Detalle::class,'tipodocumento_id');
    }
}
