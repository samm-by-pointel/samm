<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Public tracking secret (specification §7): only its hash is stored,
     * with attempt counting and temporary lockout. The plain secret exists
     * nowhere once it has been shown to the reporting person.
     */
    public function up(): void
    {
        Schema::create('tracking_credentials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('report_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('secret_hash');
            $table->unsignedSmallInteger('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_credentials');
    }
};
