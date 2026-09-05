<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('email')->constrained()->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->after('team_id')->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('manager_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
            $table->dropConstrainedForeignId('manager_id');
            $table->dropColumn('is_active');
        });
    }
};
