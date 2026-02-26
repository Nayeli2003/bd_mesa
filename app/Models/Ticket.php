<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'ticket'; // tu tabla se llama ticket (no tickets)
    protected $primaryKey = 'id_ticket';
    public $timestamps = false;

    protected $fillable = [
        'id_sucursal',
        'id_estado',
        'id_usuario',
        'id_tecnico',
        'id_prioridad',
        'id_tipo_problema',
        'titulo',
        'descripcion',
        'fecha_creacion',
        'comentarios'
    ];

    public function mensajes()
    {
        return $this->hasMany(TicketMensaje::class, 'id_ticket', 'id_ticket');
    }
}
