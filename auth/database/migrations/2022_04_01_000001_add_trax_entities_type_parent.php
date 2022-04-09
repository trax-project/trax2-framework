<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTraxEntitiesTypeParent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trax_entities', function (Blueprint $table) {
            $table->string('type', 20)->nullable();

            // Entity relation
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')
                ->references('id')
                ->on('trax_entities')
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
        Schema::table('trax_entities', function (Blueprint $table) {
            $table->dropForeign('trax_entities_parent_id_foreign');
            $table->dropColumn('type');
            $table->dropColumn('parent_id');
        });
    }
}
