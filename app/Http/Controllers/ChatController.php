<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\UpdateChatRequest;
use App\Models\Chat;
use App\Services\ChatService;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $chats = $this->chatService->listChats();

        return view('chats.index', ['chats' => $chats]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('chats.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatRequest $request)
    {
        $this->chatService->createChat($request->validated());

        return redirect()->route('chats.index')->with('success', 'Chat created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Chat $chat)
    {
        $chat->load('messages'); // Load messages for the chat

        return view('chats.show', compact('chat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chat $chat)
    {
        return view('chats.edit', compact('chat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatRequest $request, Chat $chat)
    {
        $this->chatService->updateChat($chat, $request->validated());

        return redirect()->route('chats.index')->with('success', 'Chat updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chat $chat)
    {
        $this->chatService->deleteChat($chat);

        return redirect()->route('chats.index')->with('success', 'Chat deleted successfully!');
    }
}
