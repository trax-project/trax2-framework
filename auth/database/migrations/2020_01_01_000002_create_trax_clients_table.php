<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraxClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('trax_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->json('meta');
            $table->json('permissions');
            $table->boolean('admin')->default(false);
            $table->timestamps();

            // Entity relation
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->foreign('entity_id')
                ->references('id')
                ->on('trax_entities')
                ->onDelete('restrict');

            // Owner relation
            $table->unsignedBigInteger('owner_id')->nullable()->index();
            $table->foreign('owner_id')
                ->references('id')
                ->on('trax_owners')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('trax_clients', function (Blueprint $table) {
            $table->dropForeign('trax_clients_entity_id_foreign');
            $table->dropForeign('trax_clients_owner_id_foreign');
        });
        Schema::dropIfExists('trax_clients');
    }
}
