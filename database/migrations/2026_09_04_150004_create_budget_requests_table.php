<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('initiator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('spender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('objective');
            $table->text('description');
            $table->text('expected_outcome');
            $table->decimal('amount_bdt', 14, 2);
            $table->decimal('approved_amount_bdt', 14, 2)->nullable();
            $table->date('request_date');
            $table->unsignedSmallInteger('budget_year');
            $table->unsignedTinyInteger('budget_month');
            $table->boolean('is_backdated')->default(false);
            $table->text('backdate_reason')->nullable();
            $table->text('backdate_evidence')->nullable();
            $table->date('activity_start_date');
            $table->date('activity_end_date');
            $table->string('location');
            $table->string('vendor')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('current_approval_step')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('super_admin_cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('super_admin_cleared_at')->nullable();
            $table->text('super_admin_comment')->nullable();
            $table->timestamps();

            $table->index(['budget_year', 'budget_month']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_requests');
    }
};
