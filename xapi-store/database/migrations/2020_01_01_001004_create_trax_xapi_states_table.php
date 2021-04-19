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
        });
        Schema::dropIfExists('trax_xapi_states');
    }
}
