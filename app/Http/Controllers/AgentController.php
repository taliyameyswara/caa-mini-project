<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AgentController extends Controller
{
    public function index()
    {
        return Agent::with('chatRooms')->get();
    }

    // update max customer
}
