<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraxXapiAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trax_xapi_agents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('vid');    // A virtual ID based on the xAPI identification.
            $table->json('data');
            $table->boolean('pseudonymized')->default(0);
            $table->timestamps();

            // Person relation
            $table->unsignedBigInteger('person_id')->index();
            $table->foreign('person_id')
                ->references('id')
                ->on('trax_xapi_persons')
                ->onDelete('cascade');

            // Pseudonimized agent relation
            $table->unsignedBigInteger('pseudo_id')->nullable();
            $table->foreign('pseudo_id')
                ->references('id')
                ->on('trax_xapi_agents')
                ->onDelete('set null');

            // Owner relation
            $table->unsignedBigInteger('owner_id')->nullable()->index();
            $table->foreign('owner_id')
                ->references('id')
                ->on('trax_owners')
                ->onDelete('cascade');

            // Unicity.
            $table->unique(['vid', 'owner_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trax_xapi_agents', function (Blueprint $table) {
            $table->dropForeign('trax_xapi_agents_person_id_foreign');
            $table->dropForeign('trax_xapi_agents_pseudo_id_foreign');
            $table->dropForeign('trax_xapi_agents_owner_id_foreign');
        });
        Schema::dropIfExists('trax_xapi_agents');
    }
}
