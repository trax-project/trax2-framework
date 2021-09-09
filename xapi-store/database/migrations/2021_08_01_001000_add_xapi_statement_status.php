<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddXapiStatementStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trax_xapi_statements', function (Blueprint $table) {
            $table->boolean('pending')->default(0)->index();
            $table->tinyInteger('validation')->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trax_xapi_statements', function (Blueprint $table) {
            $table->dropColumn('pending');
            $table->dropColumn('valid');
        });
    }
}
