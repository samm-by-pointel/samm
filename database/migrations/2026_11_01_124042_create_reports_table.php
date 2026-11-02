<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * What the reporting person submitted (specification §6, §14). Free-text
     * fields are encrypted at rest by the model. Deliberately no IP address
     * and no user agent. Nothing but the description is required.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference', 16)->nullable()->unique();
            $table->foreignId('report_category_id')->constrained()->restrictOnDelete();
            $table->string('confidentiality_level', 16);
            $table->string('pseudonym', 80)->nullable();
            $table->text('description');
            $table->date('facts_date')->nullable();
            $table->text('facts_period')->nullable();
            $table->text('location')->nullable();
            $table->text('people_involved')->nullable();
            $table->text('witnesses')->nullable();
            $table->boolean('is_recurring')->nullable();
            $table->boolean('is_ongoing')->nullable();
            $table->boolean('immediate_danger')->nullable();
            $table->string('locale', 5)->default('fr');
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
