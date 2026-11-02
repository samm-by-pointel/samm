<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Granular consents (specification §6, step 5): one row per purpose,
     * granted or refused, with the version of the wording shown.
     */
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->string('purpose', 32);
            $table->boolean('granted');
            $table->timestamp('granted_at')->nullable();
            $table->string('wording_version', 16);
            $table->timestamps();
            $table->unique(['report_id', 'purpose']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};
