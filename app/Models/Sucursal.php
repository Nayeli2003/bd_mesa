<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursal';
    protected $primaryKey = 'id_sucursal';
    public $timestamps = false;

    // IMPORTANTE: el id NO es autoincrement
    public $incrementing = false;
    protected $keyType = 'int';

    // 👉 ESTO ES LO QUE FALTABA
    protected $fillable = [
        'id_sucursal',
        'nombre',
    ];

    // Relación con usuarios (opcional, pero bien)
    public function usuarios()
    {
        return $this->hasMany(\App\Models\User::class, 'id_sucursal', 'id_sucursal');
    }
}
