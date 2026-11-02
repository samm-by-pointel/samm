<?php

namespace App\Modules\Privacy\Models;

use App\Modules\Core\Concerns\HasPublicIdentifier;
use App\Modules\Privacy\Enums\ConsentPurpose;
use App\Modules\Reporting\Models\Report;
use Database\Factories\ConsentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One consent decision for one purpose, with the wording version shown.
 *
 * @property ConsentPurpose $purpose
 * @property bool $granted
 * @property Carbon|null $granted_at
 * @property string $wording_version
 */
#[Fillable(['report_id', 'purpose', 'granted', 'granted_at', 'wording_version'])]
#[UseFactory(ConsentFactory::class)]
class Consent extends Model
{
    /** @use HasFactory<ConsentFactory> */
    use HasFactory, HasPublicIdentifier;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purpose' => ConsentPurpose::class,
            'granted' => 'boolean',
            'granted_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
