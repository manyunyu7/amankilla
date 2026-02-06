<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('universes', function (Blueprint $table) {
            $table->foreignId('forked_from_id')->nullable()->after('allow_fork')
                ->constrained('universes')->nullOnDelete();
            $table->unsignedInteger('fork_count')->default(0)->after('forked_from_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('universes', function (Blueprint $table) {
            $table->dropForeign(['forked_from_id']);
            $table->dropColumn(['forked_from_id', 'fork_count']);
        });
    }
};
