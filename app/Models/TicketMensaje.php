<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class TicketMensaje extends Model
{
    protected $table = 'ticket_mensaje';
    protected $primaryKey = 'id_mensaje';
    public $timestamps = false;
    protected $with = [];

    protected $fillable = [
    'id_ticket',
    'id_usuario',
    'mensaje',
    'archivo', 
    'fecha_envio',
    'leido',
];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id_ticket');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}
