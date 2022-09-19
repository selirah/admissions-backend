<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableSchoolAddSignatoryColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school', function (Blueprint $table) {
            $table->renameColumn('principal_name', 'letter_signatory');
            $table->renameColumn('principal_signature', 'letter_signature');
            $table->string('signatory_position')->default('Principal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school', function (Blueprint $table) {
            $table->renameColumn('letter_signatory', 'principal_name');
            $table->renameColumn('letter_signature', 'principal_signature');
            $table->dropColumn('signatory_position');
        });
    }
}
