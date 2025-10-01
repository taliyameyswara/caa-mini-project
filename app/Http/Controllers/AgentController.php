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

    public function updateMaxCustomers(Request $request, $id)
    {
        $request->validate([
            'max_customers' => 'required|integer|min:1'
        ]);

        $agent = Agent::findOrFail($id);
        $agent->max_customers = $request->max_customers;
        $agent->save();

        return response()->json([
            'message' => 'Max customers updated successfully',
            'agent' => $agent,
        ], 200);
    }
}
