<?php

namespace Trax\XapiStore\Events;

use Illuminate\Support\Collection;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class StatementRecordsInserted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The collection of statements inserted
     *
     * @var \Illuminate\Support\Collection
     */
    public $statements;

    /**
     * Create a new event instance.
     *
     * @param Collection $statements
     * @return void
     */
    public function __construct(Collection $statements)
    {
        $this->statements = $statements;
    }
}
