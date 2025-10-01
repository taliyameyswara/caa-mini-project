<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatRoomController extends Controller
{

    protected $qiscus_agent_id;
    protected $qiscus_app_id;
    protected $qiscus_secret_key;
    protected $qiscus_base_url;

    public function __construct()
    {
        $this->qiscus_base_url = env("QISCUS_BASE_URL");
        $this->qiscus_agent_id = env("QISCUS_AGENT_ID");
        $this->qiscus_app_id = env("QISCUS_APP_ID");
        $this->qiscus_secret_key = env("QISCUS_SECRET_KEY");
    }

    public function index()
    {
        return ChatRoom::with('agent')->get();
    }

    public function saveChatRoom(Request $request)
    {
        $payload = $request->all();

        $roomId = null;
        $isResolved = false;

        if (isset($payload['room_id'])) {
            $roomId = $payload['room_id'];
            $isResolved = $payload['is_resolved'] ?? false;
        } elseif (isset($payload['service']['room_id'])) {
            $roomId = $payload['service']['room_id'];
            $isResolved = $payload['service']['is_resolved'] ?? false;
        }

        $chatRoom = ChatRoom::updateOrCreate(
            ['qiscus_room_id' => $roomId],
            [
                'status'      => $isResolved ? 'resolved' : 'unserved',
                'resolved_at' => $isResolved ? now() : null,
            ]
        );

        return response()->json([
            'success'   => true,
            'chat_room' => $chatRoom
        ]);
    }

    public function getAllQiscusAgents()
    {
        $response = Http::withHeaders([
            "Qiscus-Secret-Key" => $this->qiscus_secret_key,
            "Qiscus-App-Id" => $this->qiscus_app_id,
        ])->get($this->qiscus_base_url . "/api/v2/admin/agents?page=&limit&search&scope");

        return $response->json();
    }

    public function assignQiscusAgent($qiscus_room_id, $qiscus_agent_id)
    {
        Http::withHeaders([
            "Qiscus-Secret-Key" => $this->qiscus_secret_key,
            "Qiscus-App-Id" => $this->qiscus_app_id,
        ])->post($this->qiscus_base_url . "/api/v1/admin/service/assign_agent", [
            'room_id' => $qiscus_room_id,
            'agent_id' => $qiscus_agent_id,
        ]);
    }

    // flow 2 -> get all agent yang ada & assign
    public function assignAgent()
    {
        //  1. hit api qiscus get all agent
        $data = $this->getAllQiscusAgents();
        $agents = $data["data"]["agents"];

        foreach ($agents as $agent) {
            Agent::firstOrCreate(
                [
                    'qiscus_agent_id' => $agent['id'],
                ]
            );
        }

        //  2. ambil semua chat room dengan status 'unserved'
        $unservedRooms = ChatRoom::where('status', 'unserved')->orderBy('created_at', 'asc')->get();

        $availableAgentIds = collect($agents)
            ->filter(fn($a) => $a['is_available'] === true)
            ->pluck('id')
            ->toArray();

        //  3. looping setiap chat_room unserved:
        foreach ($unservedRooms as $room) {
            $agent = Agent::whereIn('qiscus_agent_id', $availableAgentIds)
                ->whereRaw("(SELECT COUNT(*) FROM chat_rooms WHERE agent_id = agents.id AND status = 'served') < max_customers")
                ->first();

            if (!$agent) {
                break;
            }

            if ($agent) {
                $this->assignQiscusAgent($room->qiscus_room_id, $agent->qiscus_agent_id);

                $room->update([
                    'status' => 'served',
                    'agent_id' => $agent->id
                ]);
            }
        }
    }

    public function qiscusWebhook(Request $request)
    {

        Log::info('Qiscus Webhook Hit', $request->all());

        $this->saveChatRoom($request);
        $this->assignAgent();

        return response()->json(['success' => true]);
    }
}
