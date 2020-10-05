<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraxXapiStatementActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trax_xapi_statement_activity', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 16)->index();
            $table->boolean('sub')->default(0)->index();

            // Statement relation
            $table->unsignedBigInteger('statement_id')->index();
            $table->foreign('statement_id')
                ->references('id')
                ->on('trax_xapi_statements')
                ->onDelete('cascade');

            // Activity relation
            $table->unsignedBigInteger('activity_id')->index();
            $table->foreign('activity_id')
                ->references('id')
                ->on('trax_xapi_activities')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trax_xapi_statement_activity', function (Blueprint $table) {
            $table->dropForeign('trax_xapi_statement_activity_statement_id_foreign');
            $table->dropForeign('trax_xapi_statement_activity_activity_id_foreign');
        });
        Schema::dropIfExists('trax_xapi_statement_activity');
    }
}
