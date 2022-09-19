<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchoolTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->index()->unique();
            $table->string('school_name');
            $table->integer('category_id');
            $table->string('region');
            $table->string('town');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('sender_id');
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('principal_name');
            $table->string('principal_signature')->nullable();
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
        Schema::dropIfExists('school');
    }
}
