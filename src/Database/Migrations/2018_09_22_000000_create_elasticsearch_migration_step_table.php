<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElasticsearchMigrationStepTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('triadev_elasticsearch_migration_step', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('migration_id');
            $table->string('type');
            $table->integer('status')->default(
                \Triadev\EsMigration\Business\Mapper\MigrationStatus::MIGRATION_STATUS_WAIT
            );
            $table->text('error')->nullable();
            $table->text('params');
            $table->integer('priority')->default(1);
            $table->boolean('stop_on_failure')->default(true);
            $table->timestamps();
            
            $table->foreign('migration_id')->references('id')->on('triadev_elasticsearch_migration');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('triadev_elasticsearch_migration_step');
    }
}
