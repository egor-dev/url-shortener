<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinkHitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('link_hits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('link_id');
            $table->text('user_agent')->nullable();
            $table->string('ip', 15)->nullable();
            $table->string('session_id', 40)->nullable();
            $table->timestamps();

            $table->foreign('link_id')->references('id')->in('links')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('link_hits');
    }
}
