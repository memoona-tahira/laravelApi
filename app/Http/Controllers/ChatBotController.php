<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\ChatHistory;

class ChatBotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $token = $request->bearerToken();

        if ($token) {
            $user = null;
            $accessToken = PersonalAccessToken::findToken($token);

            if (!$accessToken) {
                return response()->json([
                    'error' => 'Invalid or expired token',
                    'message' => 'Please login again to get a new token'
                ], 401);
            }

            $user = $accessToken->tokenable;

            if (!$user) {
                return response()->json([
                    'error' => 'User not found',
                    'message' => 'The user associated with this token no longer exists'
                ], 401);
            }

            $session_id = $accessToken->id;

            return $this->handleAuthenticatedChat($request, $session_id, $user->id);
        }

        return $this->handleGuestChat($request);
    }

    private function handleGuestChat(Request $request)
    {
        // For guest users, just send a single message without history
        $response = Http::post('http://localhost:11434/api/generate', [
            'model' => 'mistral',
            'prompt' => $request->message,
            'stream' => false
        ]);

        $responseData = json_decode($response->body(), true);
        $botResponse = $responseData['response'];

        return response()->json([
            'response' => $botResponse
        ]);
    }

    private function handleAuthenticatedChat(Request $request, $sessionId, $user_id)
    {
        // Get previous messages for this session
        $previousMessages = ChatHistory::where('user_id', $user_id)
            ->where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($chat) => [
                ['role' => 'user', 'content' => $chat->user_message],
                ['role' => 'assistant', 'content' => $chat->bot_response],
            ])
            ->flatten(1)
            ->toArray();

        $messages = array_merge($previousMessages, [
            ['role' => 'user', 'content' => $request->message]
        ]);

        $response = Http::post('http://localhost:11434/api/chat', [
            'model' => 'mistral',
            'messages' => $messages,
            'stream' => false,
        ]);

        // Parse the response
        $responseData = json_decode($response->body(), true);
        $botResponse = $responseData['message']['content'];

        // Store the conversation in the database
        ChatHistory::create([
            'user_id' => $user_id,
            'session_id' => $sessionId,
            'user_message' => $request->message,
            'bot_response' => $botResponse
        ]);

        return response()->json([
            'response' => $botResponse
        ]);
    }
}
