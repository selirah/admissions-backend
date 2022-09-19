<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentAccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_access', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('school_id')->index();
            $table->string('application_number')->index();
            $table->string('pin');
            $table->tinyInteger('accessed');
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
        Schema::dropIfExists('student_access');
    }
}
