<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_setting', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 20)->collation('utf8_general_ci')->nullable();
            $table->string('parent', 20)->collation('utf8_general_ci')->comment('父层');
            $table->enum('level', ['0','1'])->comment('0部门 1组别');
            $table->enum('status', ['0','1']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_setting');
    }
}
