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
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('level')->default('easy')->change();
            $table->string('question_text')->nullable();
            $table->string('correct_answer')->nullable();
            $table->text('options')->nullable();
            $table->string('explain')->nullable();
            $table->string('type')->nullable();
            $table->string('ipa')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->integer('level')->default(1)->change();

            $table->dropColumn('question_text');
            $table->dropColumn('correct_answer');
            $table->dropColumn('options');
            $table->dropColumn('explain');
            $table->dropColumn('type');
            $table->dropColumn('ipa');
        });
    }
};
