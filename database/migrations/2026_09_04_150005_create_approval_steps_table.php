<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_request_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->text('comment')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->unique(['budget_request_id', 'step_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_steps');
    }
};
