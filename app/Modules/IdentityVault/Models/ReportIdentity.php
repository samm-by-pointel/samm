<?php

namespace App\Modules\IdentityVault\Models;

use App\Modules\Core\Concerns\HasPublicIdentifier;
use App\Modules\Reporting\Models\Report;
use Database\Factories\ReportIdentityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Identity of the reporting person, kept apart from the report content.
 * Every read in the manager's space must be explicit, justified and audited.
 *
 * @property string $full_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $preferred_contact
 */
#[Fillable(['report_id', 'full_name', 'email', 'phone', 'preferred_contact'])]
#[UseFactory(ReportIdentityFactory::class)]
class ReportIdentity extends Model
{
    /** @use HasFactory<ReportIdentityFactory> */
    use HasFactory, HasPublicIdentifier;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'full_name' => 'encrypted',
            'email' => 'encrypted',
            'phone' => 'encrypted',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Masked representation for screens where the identity must stay hidden (charter §12.10).
     */
    public function masked(): string
    {
        return '•••••• ••••••';
    }
}
