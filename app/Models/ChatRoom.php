<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $table = "chat_rooms";
    protected $fillable = ['qiscus_room_id', 'agent_id', 'status', 'resolved_at'];

    public function agent()
    {
        return $this->belongsTo(Agent::class, "agent_id", "id");
    }
}
