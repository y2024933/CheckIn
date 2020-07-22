<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_setting', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 20)->collation('utf8_general_ci');
            $table->time('starttime');
            $table->time('endtime');
            $table->enum('crossday', ['0', '1'])->default('0')->comment('跨天');
            $table->enum('status', ['0', '1']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shift_setting');
    }
}
