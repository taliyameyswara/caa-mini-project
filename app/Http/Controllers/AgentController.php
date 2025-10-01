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
        try {
            $request->validate([
                'max_customers' => 'required|integer|min:1'
            ]);

            $agent = Agent::findOrFail($id);
            $agent->max_customers = $request->max_customers;
            $agent->save();

            return response()->json([
                'success' => true,
                'message' => 'Max customers updated successfully',
                'data'    => $agent,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
