<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTraxAccessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('trax_accesses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('cors');
            $table->boolean('active')->default(true);
            $table->json('meta');
            $table->json('permissions');
            $table->boolean('admin')->default(false);
            $table->boolean('inherited_permissions')->default(true);
            $table->timestamps();

            // Credentials provider relation.
            $table->unsignedBigInteger('credentials_id');
            $table->string('credentials_type');

            // Client relation.
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')
                ->references('id')
                ->on('trax_clients')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('trax_accesses', function (Blueprint $table) {
            $table->dropForeign('trax_accesses_client_id_foreign');
        });
        Schema::dropIfExists('trax_accesses');
    }
}
