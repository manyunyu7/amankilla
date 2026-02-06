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
        Schema::create('scenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timeline_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('content'); // Rich text (HTML/JSON from TipTap)
            $table->text('summary')->nullable();
            $table->integer('order');

            // Metadata
            $table->string('date')->nullable(); // In-story date
            $table->string('time')->nullable(); // In-story time
            $table->string('location')->nullable();
            $table->string('mood')->nullable(); // warm, tense, playful, sad
            $table->string('pov')->nullable(); // Point of view
            $table->integer('word_count')->default(0);

            // Branch info
            $table->boolean('is_branch_point')->default(false);
            $table->string('branch_question')->nullable();
            $table->timestamps();

            $table->index('order');
        });

        // Add foreign key to timelines.branch_from_id
        Schema::table('timelines', function (Blueprint $table) {
            $table->foreign('branch_from_id')->references('id')->on('scenes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timelines', function (Blueprint $table) {
            $table->dropForeign(['branch_from_id']);
        });

        Schema::dropIfExists('scenes');
    }
};
