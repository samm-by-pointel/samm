<?php

namespace App\Modules\Reporting\Models;

use App\Modules\Cases\Models\CaseFile;
use App\Modules\Core\Concerns\HasPublicIdentifier;
use App\Modules\IdentityVault\Models\ReportIdentity;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Privacy\Models\Consent;
use App\Modules\Reporting\Enums\ConfidentialityLevel;
use App\Modules\Tracking\Models\TrackingCredential;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * What the reporting person submitted. The organization's handling lives in
 * the case (1:1), the identity in the vault, never here.
 *
 * @property string|null $reference
 * @property ConfidentialityLevel $confidentiality_level
 * @property string|null $pseudonym
 * @property string $description
 * @property Carbon|null $facts_date
 * @property string|null $facts_period
 * @property string|null $location
 * @property string|null $people_involved
 * @property string|null $witnesses
 * @property bool|null $is_recurring
 * @property bool|null $is_ongoing
 * @property bool|null $immediate_danger
 * @property string $locale
 * @property Carbon|null $submitted_at
 */
#[Fillable([
    'reference', 'report_category_id', 'confidentiality_level', 'pseudonym', 'description', 'facts_date', 'facts_period',
    'location', 'people_involved', 'witnesses', 'is_recurring', 'is_ongoing', 'immediate_danger', 'locale', 'submitted_at',
])]
#[UseFactory(ReportFactory::class)]
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory, HasPublicIdentifier;

    /**
     * Free-text fields are encrypted at rest (APP_KEY lives outside the database).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'confidentiality_level' => ConfidentialityLevel::class,
            'description' => 'encrypted',
            'facts_date' => 'date',
            'facts_period' => 'encrypted',
            'location' => 'encrypted',
            'people_involved' => 'encrypted',
            'witnesses' => 'encrypted',
            'is_recurring' => 'boolean',
            'is_ongoing' => 'boolean',
            'immediate_danger' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ReportCategory::class, 'report_category_id');
    }

    public function case(): HasOne
    {
        return $this->hasOne(CaseFile::class);
    }

    /**
     * The identity is in a separate vault: load it explicitly, never by default.
     */
    public function identity(): HasOne
    {
        return $this->hasOne(ReportIdentity::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class);
    }

    public function trackingCredential(): HasOne
    {
        return $this->hasOne(TrackingCredential::class);
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }

    public function isAnonymous(): bool
    {
        return $this->confidentiality_level === ConfidentialityLevel::Anonymous;
    }

    /**
     * How the reporting person is named in the manager's space: pseudonym,
     * or a neutral wording. Never the real name here.
     */
    public function displayName(): string
    {
        return $this->pseudonym ?: __('reporting.anonymous_person');
    }
}
