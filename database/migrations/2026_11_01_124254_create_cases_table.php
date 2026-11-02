<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The organization's handling of a report (specification §8): one case
     * per report. Status values come from the Workflow enum.
     */
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('report_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('new')->index();
            $table->string('priority', 16)->default('normal');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('closure_reason')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
