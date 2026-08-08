<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSongsTable extends Migration
{
    public function up()
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist');
            $table->string('audio_file_path');
            $table->string('instrumental_file_path');
            $table->string('thumbnail')->nullable();
            $table->integer('difficulty')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('songs');
    }

};
