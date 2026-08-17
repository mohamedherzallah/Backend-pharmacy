<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    private function formatConversation(Conversation $conv): array
    {
        return [
            'id' => $conv->id,
            'user_id' => $conv->user_id,
            'pharmacy_id' => $conv->pharmacy_id,
            'created_at' => $conv->created_at,
            'updated_at' => $conv->updated_at,
            'pharmacy' => $conv->pharmacy ? [
                'id' => $conv->pharmacy->id,
                'pharmacy_name' => $conv->pharmacy->pharmacy_name,
                'logo' => $conv->pharmacy->logo ? asset('storage/' . $conv->pharmacy->logo) : null,
            ] : null,
            'user' => $conv->user ? [
                'id' => $conv->user->id,
                'name' => $conv->user->name,
                'avatar' => $conv->user->avatar ? asset('uploads/avatars/' . $conv->user->avatar) : null,
            ] : null,
            'messages' => $conv->messages,
        ];
    }

    // قائمة المحادثات للمستخدم
    public function index(Request $request)
    {
        $convs = Conversation::where(function($q) use($request){
            $q->where('user_id', $request->user()->id)
                ->orWhere('pharmacy_id', optional($request->user()->pharmacy)->id);
        })
            // 'pharmacy'/'user' are needed so the frontend can show who each
            // conversation is with (name/logo) - previously only 'messages'
            // was loaded, so there was no way to render a conversation list
            // without a second request per conversation.
            ->with(['pharmacy:id,pharmacy_name,logo', 'user:id,name,avatar', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->latest()
            ->get();

        return response()->json($convs->map(fn ($c) => $this->formatConversation($c)));
    }

    // انشاء محادثة جديدة (عند الحاجة)
    public function store(Request $request)
    {
        $request->validate([
            'pharmacy_id' => 'required|exists:pharmacies,id',
        ]);

        $conv = Conversation::firstOrCreate([
            'user_id' => $request->user()->id,
            'pharmacy_id' => $request->pharmacy_id,
        ]);

        $conv->load(['pharmacy:id,pharmacy_name,logo', 'user:id,name,avatar']);

        return response()->json($this->formatConversation($conv), 201);
    }
}
