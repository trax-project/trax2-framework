<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraxXapiStatementCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trax_xapi_statement_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('iri');
            $table->boolean('profile')->default(false);
            $table->timestamps();

            // Owner relation
            $table->unsignedBigInteger('owner_id')->nullable()->index();
            $table->foreign('owner_id')
                ->references('id')
                ->on('trax_owners')
                ->onDelete('cascade');

            // Unicity.
            $table->unique(['iri', 'owner_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trax_xapi_statement_categories', function (Blueprint $table) {
            $table->dropForeign('trax_xapi_statement_categories_owner_id_foreign');
        });
        Schema::dropIfExists('trax_xapi_statement_categories');
    }
}
