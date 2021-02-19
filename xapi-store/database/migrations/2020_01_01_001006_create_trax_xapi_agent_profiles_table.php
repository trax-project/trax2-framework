<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraxXapiAgentProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trax_xapi_agent_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('profile_id');
            $table->string('vid')->index();    // A virtual ID based on the xAPI identification.
            $table->json('agent');
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
            $table->unique(['vid', 'profile_id', 'owner_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trax_xapi_agent_profiles', function (Blueprint $table) {
            $table->dropForeign('trax_xapi_agent_profiles_owner_id_foreign');
            $table->dropForeign('trax_xapi_agent_profiles_entity_id_foreign');
            $table->dropForeign('trax_xapi_agent_profiles_client_id_foreign');
            $table->dropForeign('trax_xapi_agent_profiles_access_id_foreign');
        });
        Schema::dropIfExists('trax_xapi_agent_profiles');
    }
}
