<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actual_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount_bdt', 14, 2);
            $table->date('spend_date');
            $table->string('vendor');
            $table->text('description');
            $table->text('overrun_justification')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actual_expenses');
    }
};
