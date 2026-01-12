<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemorizationAIResponse implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sessionId;
    public $chunk;
    public $userId;
    public $mistakes;
    public $isComplete;

    /**
     * Create a new event instance.
     */
    public function __construct(int $sessionId, string $chunk, int $userId, array $mistakes = [], bool $isComplete = false)
    {
        $this->sessionId = $sessionId;
        $this->chunk = $chunk;
        $this->userId = $userId;
        $this->mistakes = $mistakes;
        $this->isComplete = $isComplete;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('memorization.user.' . $this->userId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'memorization.ai-response';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->sessionId,
            'chunk' => $this->chunk,
            'mistakes' => $this->mistakes,
            'is_complete' => $this->isComplete,
            // Format for Flutter to easily identify mistakes in sentence
            'mistakes_detail' => array_map(function ($mistake) {
                return [
                    'type' => $mistake['type'] ?? 'wrong_word',
                    'word_position' => $mistake['word_position'] ?? null,
                    'sentence_position' => $mistake['sentence_position'] ?? null,
                    'original_word' => $mistake['original_word'] ?? '',
                    'user_word' => $mistake['user_word'] ?? null,
                    'confidence' => $mistake['confidence'] ?? 0,
                    'suggestion' => $mistake['suggestion'] ?? '',
                ];
            }, $this->mistakes),
        ];
    }
}
