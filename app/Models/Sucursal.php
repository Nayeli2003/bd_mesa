<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursal';
    protected $primaryKey = 'id_sucursal';
    public $timestamps = false;

    public $incrementing = false; //IMPORTANTE PARA QUE NO LO HAGA AUTOMATICAMENTE
    protected $keyType = 'int';

    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_sucursal', 'id_sucursal');
    }
}
