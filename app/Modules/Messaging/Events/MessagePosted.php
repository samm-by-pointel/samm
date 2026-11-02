<?php

namespace App\Modules\Messaging\Events;

use App\Modules\Messaging\Models\Message;
use Illuminate\Foundation\Events\Dispatchable;

class MessagePosted
{
    use Dispatchable;

    public function __construct(public readonly Message $message) {}
}
