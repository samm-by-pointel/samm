<?php

namespace App\Modules\Organizations\Models;

use App\Modules\Core\Concerns\HasPublicIdentifier;
use App\Modules\Core\Concerns\HasTranslations;
use Database\Factories\EmergencyContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An emergency number shown in the "immediate danger" block.
 *
 * @property array<string, string> $label
 * @property string $phone_number
 * @property int $position
 * @property bool $is_active
 */
#[Fillable(['label', 'phone_number', 'position', 'is_active'])]
#[UseFactory(EmergencyContactFactory::class)]
class EmergencyContact extends Model
{
    /** @use HasFactory<EmergencyContactFactory> */
    use HasFactory, HasPublicIdentifier, HasTranslations;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'label' => 'array',
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function label(): ?string
    {
        return $this->translate('label');
    }

    /**
     * Phone number usable in a `tel:` link (digits and leading + only).
     */
    public function dialable(): string
    {
        return preg_replace('/[^0-9+]/', '', $this->phone_number) ?? '';
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    #[Scope]
    protected function ordered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }
}
