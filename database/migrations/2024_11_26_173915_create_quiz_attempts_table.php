<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lesson_id');
            $table->float('score')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamp('quiz_started_at')->nullable();
            $table->timestamp('quiz_ended_at')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamps();

            // Ensure only one attempt per user per lesson
            $table->unique(['user_id', 'lesson_id']);

            // Foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('lesson_id')
                ->references('id')
                ->on('lessons')
                ->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
