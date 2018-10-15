<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElasticsearchMigrationsCreateIndexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('triadev_elasticsearch_migration_step_create_index', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('migration_step_id')->unique();
            $table->string('mappings');
            $table->string('settings')->nullable();
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
        Schema::drop('triadev_elasticsearch_migration_step_create_index');
    }
}
