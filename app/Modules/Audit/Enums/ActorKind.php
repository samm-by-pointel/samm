<?php

namespace App\Modules\Audit\Enums;

/**
 * Who performed an audited action: an internal account, a reporting person
 * (identified only by their case, never by name) or the system itself.
 */
enum ActorKind: string
{
    case User = 'user';
    case Reporter = 'reporter';
    case System = 'system';
}
