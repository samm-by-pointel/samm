<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Emergency numbers shown in the "immediate danger" block of the public
     * space. Managed by the organization administrator, never hard-coded.
     */
    public function up(): void
    {
        Schema::create('emergency_contacts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->json('label');
            $table->string('phone_number', 32);
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
    }
};
