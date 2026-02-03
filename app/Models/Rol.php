<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rol';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    // Porque el id_rol lo asignas manualmente
    public $incrementing = false;
    protected $keyType = 'int';

    // Campos permitidos para asignación masiva
    protected $fillable = [
        'id_rol',
        'nombre_rol',
    ];
}
