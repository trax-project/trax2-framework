<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraxXapiLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trax_xapi_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('api')->index();
            $table->string('method')->index();
            $table->unsignedInteger('count')->nullable();   // To count items of batches.
            $table->boolean('error')->default(0)->index();
            $table->json('data')->nullable();               // To store error data.
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
                ->onDelete('cascade');

            // Client relation
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->foreign('client_id')
                ->references('id')
                ->on('trax_clients')
                ->onDelete('cascade');

            // Access relation
            $table->unsignedBigInteger('access_id')->nullable()->index();
            $table->foreign('access_id')
                ->references('id')
                ->on('trax_accesses')
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
        Schema::table('trax_xapi_logs', function (Blueprint $table) {
            $table->dropForeign('trax_xapi_logs_owner_id_foreign');
            $table->dropForeign('trax_xapi_logs_entity_id_foreign');
            $table->dropForeign('trax_xapi_logs_client_id_foreign');
            $table->dropForeign('trax_xapi_logs_access_id_foreign');
        });
        Schema::dropIfExists('trax_xapi_logs');
    }
}
