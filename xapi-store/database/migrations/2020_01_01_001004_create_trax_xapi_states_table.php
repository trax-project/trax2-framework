<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraxXapiStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trax_xapi_states', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('state_id');
            $table->string('activity_id')->index();
            $table->string('vid')->index();    // A virtual ID based on the xAPI identification.
            $table->json('agent');
            $table->uuid('registration')->nullable();
            $table->json('data');
            $table->string('timestamp');
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
                ->onDelete('set null');

            // Client relation
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->foreign('client_id')
                ->references('id')
                ->on('trax_clients')
                ->onDelete('set null');

            // Access relation
            $table->unsignedBigInteger('access_id')->nullable()->index();
            $table->foreign('access_id')
                ->references('id')
                ->on('trax_accesses')
                ->onDelete('set null');

            // Unicity.
            $table->unique(['vid', 'activity_id', 'state_id', 'registration', 'owner_id'], 'trax_xapi_states_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trax_xapi_states', function (Blueprint $table) {
            $table->dropForeign('trax_xapi_states_owner_id_foreign');
            $table->dropForeign('trax_xapi_states_entity_id_foreign');
            $table->dropForeign('trax_xapi_states_client_id_foreign');
            $table->dropForeign('trax_xapi_states_access_id_foreign');
        });
        Schema::dropIfExists('trax_xapi_states');
    }
}
