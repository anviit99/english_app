<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('user_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lesson_id')->nullable(); // nếu có bài học cụ thể
            $table->float('percent');   // VD: 80.0
            $table->float('score');     // VD: 8.0
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->integer('time_spent');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // nếu có bảng lessons
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_scores');
    }
};
