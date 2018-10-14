<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElasticsearchMigrationsReindexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('triadev_elasticsearch_migration_step_reindex', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('migration_step_id')->unique();
            $table->string('dest_index');
            $table->boolean('refresh_source_index');
            $table->string('global');
            $table->string('source');
            $table->string('dest');
            $table->timestamps();
            
            $table->foreign('migration_step_id')->references('migration_id')->on('triadev_elasticsearch_migration_steps');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('triadev_elasticsearch_migration_step_reindex');
    }
}
