<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * One installation serves exactly one organization: the `single_row`
     * column is always true and unique, so the database itself refuses a
     * second row.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->json('service_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->nullable();
            $table->string('secondary_color', 7)->nullable();
            $table->string('neutral_title')->nullable();
            $table->string('quick_exit_url', 2048)->nullable();
            $table->string('default_locale', 5)->default('fr');
            $table->boolean('single_row')->default(true)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
