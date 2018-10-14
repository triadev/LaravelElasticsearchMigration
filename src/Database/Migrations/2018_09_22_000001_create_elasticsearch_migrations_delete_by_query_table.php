<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElasticsearchMigrationsDeleteByQueryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('triadev_elasticsearch_migrations_delete_by_query', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('migrations_id')->unique();
            $table->string('query');
            $table->string('type')->nullable();
            $table->string('options');
            $table->timestamps();
            
            $table->foreign('migrations_id')->references('migration_id')->on('triadev_elasticsearch_migrations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('triadev_elasticsearch_migrations_delete_by_query');
    }
}