<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'system_prompt' => $this->system_prompt,
            // 'user_id' => $this->user_id, // Optional
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'messages' => ChatMessageResource::collection($this->whenLoaded('messages')),
            'latest_message' => new ChatMessageResource($this->whenLoaded('latestMessage')),
        ];
    }
}
