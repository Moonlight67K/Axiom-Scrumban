<?php

namespace App\Events;

use App\Models\Card;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CardMoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $cardId;
    public $fromColumnId;
    public $toColumnId;
    public $newPosition;

    /**
     * Create a new event instance.
     */
    public function __construct($cardId, $fromColumnId, $toColumnId, $newPosition)
    {
        $this->cardId = $cardId;
        $this->fromColumnId = $fromColumnId;
        $this->toColumnId = $toColumnId;
        $this->newPosition = $newPosition;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // For simplicity using a public channel for now, will upgrade to Private in Sprint 3/Auth refinement.
        return [
            new Channel('board.' . Card::find($this->cardId)->column->board_id),
        ];
    }
}
