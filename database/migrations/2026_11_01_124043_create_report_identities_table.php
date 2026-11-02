<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Identity of the reporting person, stored apart from the report content
     * (specification §5.3), encrypted at rest, one row at most per report.
     * Access is reserved to explicitly authorized roles and logged.
     */
    public function up(): void
    {
        Schema::create('report_identities', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('report_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('full_name');
            $table->text('email')->nullable();
            $table->text('phone')->nullable();
            $table->string('preferred_contact', 16)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_identities');
    }
};
