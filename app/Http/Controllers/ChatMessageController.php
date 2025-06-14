<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChatMessageRequest;
use App\Models\Chat;
use App\Services\ChatService;

class ChatMessageController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Store a newly created message in storage for a specific chat.
     */
    public function store(StoreChatMessageRequest $request, Chat $chat)
    {
        $this->chatService->addMessageToChat($chat, 'user', $request->validated('content'));
        // Optionally, get AI reply immediately after user message
        $this->chatService->getAiReply($chat, $request->validated('content'));

        return redirect()->route('chats.show', $chat->id)->with('success', 'Message sent!');
    }
}
