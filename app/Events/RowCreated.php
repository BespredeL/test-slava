<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RowCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Row data
     *
     * @var array
     */
    public array $rowData;

    public function __construct(array $rowData)
    {
        $this->rowData = $rowData;
    }

    /**
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel('rows');
    }

    /**
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return $this->rowData;
    }
}