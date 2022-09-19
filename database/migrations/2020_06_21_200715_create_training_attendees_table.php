<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainingAttendeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_attendees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('training_list_id');
            $table->string('name_one');
            $table->string('phone_one');
            $table->longText('picture_one')->nullable();
            $table->string('name_two')->nullable();
            $table->string('phone_two')->nullable();
            $table->longText('picture_two')->nullable();
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
        Schema::dropIfExists('training_attendees');
    }
}
