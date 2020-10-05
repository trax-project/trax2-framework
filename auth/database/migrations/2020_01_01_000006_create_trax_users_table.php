<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraxUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trax_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('firstname');
            $table->string('lastname');
            $table->boolean('active')->default(1);
            $table->boolean('admin')->default(0);
            $table->string('source', 20)->default('internal')->index();
            $table->json('meta');
            $table->rememberToken();
            $table->timestamps();

            // Role relation.
            $table->unsignedBigInteger('role_id')->nullable();
            $table->foreign('role_id')
                ->references('id')
                ->on('trax_roles')
                ->onDelete('restrict');

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
    public function down()
    {
        Schema::table('trax_users', function (Blueprint $table) {
            $table->dropForeign('trax_users_role_id_foreign');
            $table->dropForeign('trax_users_entity_id_foreign');
            $table->dropForeign('trax_users_owner_id_foreign');
        });
        Schema::dropIfExists('trax_users');
    }
}
