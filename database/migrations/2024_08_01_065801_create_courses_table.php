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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('user_token', 50);
            $table->string('name', 200);
            $table->string('thumbnail', 150);
            $table->string('video', 150)->nullable();
            $table->string('description')->nullable();
            $table->smallInteger('type_id');
            $table->float('price');
            $table->smallInteger('lesson_num')->nullable();
            $table->smallInteger('video_length')->nullable();
            $table->smallInteger('follow')->nullable();
            $table->smallInteger('score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
