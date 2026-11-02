<?php

namespace App\Modules\Messaging\Models;

use App\Modules\Core\Concerns\HasPublicIdentifier;
use App\Modules\Messaging\Enums\MessageAuthor;
use App\Modules\Reporting\Models\Report;
use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * The exchange between the reporting person and the organization, one per report.
 *
 * @property int $report_id
 * @property Carbon|null $last_message_at
 */
#[Fillable(['report_id', 'last_message_at'])]
#[UseFactory(ConversationFactory::class)]
class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory, HasPublicIdentifier;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('id');
    }

    public function unreadForReporter(): int
    {
        return $this->messages()->where('author_type', MessageAuthor::Staff->value)->whereNull('read_by_reporter_at')->count();
    }

    public function unreadForStaff(): int
    {
        return $this->messages()->where('author_type', MessageAuthor::Reporter->value)->whereNull('read_by_staff_at')->count();
    }

    public function markReadByReporter(): void
    {
        $this->messages()->where('author_type', MessageAuthor::Staff->value)->whereNull('read_by_reporter_at')->toBase()->update(['read_by_reporter_at' => now()]);
    }

    public function markReadByStaff(): void
    {
        $this->messages()->where('author_type', MessageAuthor::Reporter->value)->whereNull('read_by_staff_at')->toBase()->update(['read_by_staff_at' => now()]);
    }
}
