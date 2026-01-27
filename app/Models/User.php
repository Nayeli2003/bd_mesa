<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // tabla
    protected $table = 'usuario';

    // la PK
    protected $primaryKey = 'id_usuario';

    // Si NO tienes created_at / updated_at en esa tabla
    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'id_sucursal',
        'id_rol',
    ];

    protected $hidden = [
        'password',
    ];

    // Para que Laravel use "username" en vez de "email"
    public function getAuthIdentifierName()
    {
        return 'username';
    }

    // Relación con rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    // Relación con sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }
}
