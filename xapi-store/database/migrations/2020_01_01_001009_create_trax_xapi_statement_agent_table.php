<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraxXapiStatementAgentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trax_xapi_statement_agent', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 16)->index();
            $table->boolean('sub')->default(0)->index();
            $table->boolean('group')->default(0)->index();

            // Statement relation
            $table->unsignedBigInteger('statement_id')->index();
            $table->foreign('statement_id')
                ->references('id')
                ->on('trax_xapi_statements')
                ->onDelete('cascade');

            // Agent relation
            $table->unsignedBigInteger('agent_id')->index();
            $table->foreign('agent_id')
                ->references('id')
                ->on('trax_xapi_agents')
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
        Schema::table('trax_xapi_statement_agent', function (Blueprint $table) {
            $table->dropForeign('trax_xapi_statement_agent_statement_id_foreign');
            $table->dropForeign('trax_xapi_statement_agent_agent_id_foreign');
        });
        Schema::dropIfExists('trax_xapi_statement_agent');
    }
}
