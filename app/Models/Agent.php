<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $table = "agents";
    protected $fillable = ['max_customers', 'qiscus_agent_id', 'is_available'];

    public function chatRooms()
    {
        return $this->hasMany(ChatRoom::class, "agent_id", "id");
    }
}
