<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElasticsearchMigrationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('triadev_elasticsearch_migration', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('migration');
            $table->integer('status')->default(
                \Triadev\EsMigration\Business\Mapper\MigrationStatus::MIGRATION_STATUS_WAIT
            );
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('triadev_elasticsearch_migration');
    }
}
