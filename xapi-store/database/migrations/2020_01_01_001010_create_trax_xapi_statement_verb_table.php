<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraxXapiStatementVerbTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trax_xapi_statement_verb', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('sub')->default(0)->index();

            // Statement relation
            $table->unsignedBigInteger('statement_id')->index();
            $table->foreign('statement_id')
                ->references('id')
                ->on('trax_xapi_statements')
                ->onDelete('cascade');

            // Verb relation
            $table->unsignedBigInteger('verb_id')->index();
            $table->foreign('verb_id')
                ->references('id')
                ->on('trax_xapi_verbs')
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
        Schema::table('trax_xapi_statement_verb', function (Blueprint $table) {
            $table->dropForeign('trax_xapi_statement_verb_statement_id_foreign');
            $table->dropForeign('trax_xapi_statement_verb_verb_id_foreign');
        });
        Schema::dropIfExists('trax_xapi_statement_verb');
    }
}
