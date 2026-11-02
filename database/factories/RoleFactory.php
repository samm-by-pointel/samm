<?php

namespace Database\Factories;

use App\Modules\AccessControl\Enums\Permission;
use App\Modules\AccessControl\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $key = fake()->unique()->slug(2);

        return [
            'key' => $key,
            'name' => ['fr' => 'Rôle '.$key, 'en' => 'Role '.$key],
            'description' => null,
            'is_system' => false,
        ];
    }

    public function withPermissions(Permission ...$permissions): static
    {
        return $this->afterCreating(fn (Role $role) => $role->grant(...$permissions));
    }
}
