<?php

namespace App\Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Public, non-sequential identifier for models exposed in URLs.
 *
 * The primary key stays an internal auto-increment integer that is never
 * exposed; the `uuid` column is generated on creation and used as route key.
 */
trait HasPublicIdentifier
{
    use HasUuids;

    /**
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
