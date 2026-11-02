<?php

namespace App\Modules\Reporting\Models;

use App\Modules\Core\Concerns\HasPublicIdentifier;
use App\Modules\Core\Concerns\HasTranslations;
use Database\Factories\ReportCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A kind of situation a person can report (first step of the public form).
 *
 * @property array<string, string> $name
 * @property array<string, string>|null $description
 * @property int $position
 * @property bool $is_active
 */
#[Fillable(['name', 'description', 'position', 'is_active'])]
#[UseFactory(ReportCategoryFactory::class)]
class ReportCategory extends Model
{
    /** @use HasFactory<ReportCategoryFactory> */
    use HasFactory, HasPublicIdentifier, HasTranslations;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function name(): ?string
    {
        return $this->translate('name');
    }

    public function description(): ?string
    {
        return $this->translate('description');
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
