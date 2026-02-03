<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // Nombre de la tabla real
    protected $table = 'usuario';

    // PK personalizada
    protected $primaryKey = 'id_usuario';

    // No tienes created_at / updated_at
    public $timestamps = false;

    // Campos permitidos para asignación masiva
    protected $fillable = [
        'nombre',
        'username',
        'password',
        'id_rol',
        'id_sucursal',
        'activo',
    ];

    // Ocultar password en respuestas JSON
    protected $hidden = [
        'password',
    ];

    // Casts (útil para que activo sea boolean)
    protected $casts = [
        'activo' => 'boolean',
    ];

    // Para que Laravel autentique con "username" en vez de "email"
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
