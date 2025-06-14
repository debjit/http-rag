@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-4">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h1 class="text-2xl font-bold mb-4">Chat: {{ $chat->title ?? 'Untitled Chat' }}</h1>

            <div class="border-t border-gray-200 pt-4 mt-4">
                <h2 class="text-xl font-semibold mb-3">Messages</h2>
                <div class="space-y-4">
                    @forelse ($chat->messages as $message)
                        <div class="p-3 rounded-lg {{ $message->role === 'user' ? 'bg-blue-100 text-blue-800 self-end' : 'bg-gray-100 text-gray-800 self-start' }}">
                            <p class="font-bold capitalize">{{ $message->role }}:</p>
                            <p>{{ $message->content }}</p>
                        </div>
                    @empty
                        <p class="text-gray-600">No messages in this chat yet.</p>
                    @endforelse
                </div>

                <form action="{{ route('chat-messages.store', $chat->id) }}" method="POST" class="mt-6">
                    @csrf
                    <div class="mb-4">
                        <label for="content" class="block text-sm font-medium text-gray-700">New Message</label>
                        <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" id="content" name="content" rows="3" required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">Send Message</button>
                </form>
            </div>

            <div class="mt-6">
                <a href="{{ route('chats.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">Back to Chats</a>
            </div>
        </div>
    </div>
@endsection
