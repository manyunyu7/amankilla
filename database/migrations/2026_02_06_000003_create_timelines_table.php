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
        Schema::create('timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('universe_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // "Canon", "What if breakup"
            $table->text('description')->nullable();
            $table->boolean('is_canon')->default(false);
            $table->string('color')->nullable(); // For graph visualization
            $table->unsignedBigInteger('branch_from_id')->nullable();
            $table->timestamps();

            // Will add foreign key after scenes table is created
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timelines');
    }
};
