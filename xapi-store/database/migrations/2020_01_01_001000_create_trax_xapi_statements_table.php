<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraxXapiStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trax_xapi_statements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->json('data');
            $table->boolean('voided')->default(false)->index();
            $table->timestamps();

            // Owner relation
            $table->unsignedBigInteger('owner_id')->nullable()->index();
            $table->foreign('owner_id')
                ->references('id')
                ->on('trax_owners')
                ->onDelete('cascade');

            // Entity relation
            $table->unsignedBigInteger('entity_id')->nullable()->index();
            $table->foreign('entity_id')
                ->references('id')
                ->on('trax_entities')
                ->onDelete('restrict');

            // Client relation
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->foreign('client_id')
                ->references('id')
                ->on('trax_clients')
                ->onDelete('restrict');

            // Access relation
            $table->unsignedBigInteger('access_id')->nullable()->index();
            $table->foreign('access_id')
                ->references('id')
                ->on('trax_accesses')
                ->onDelete('restrict');

            // Unicity.
            $table->unique(['uuid', 'owner_id']);
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
            $table->dropForeign('trax_xapi_statements_owner_id_foreign');
            $table->dropForeign('trax_xapi_statements_entity_id_foreign');
            $table->dropForeign('trax_xapi_statements_client_id_foreign');
            $table->dropForeign('trax_xapi_statements_access_id_foreign');
        });
        Schema::dropIfExists('trax_xapi_statements');
    }
}
