<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTraxClientsVisibleCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trax_clients', function (Blueprint $table) {
            $table->boolean('visible')->default(1);
            $table->string('category')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trax_clients', function (Blueprint $table) {
            $table->dropColumn('visible');
            $table->dropColumn('category');
        });
    }
}
