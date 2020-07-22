<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('do_list', function (Blueprint $table) {
            $table->increments('id');
            $table->string('accountid', 20)->collation('utf8_general_ci');
            $table->text('content')->collation('utf8_general_ci')->comment('待辦事項');
            $table->timestamp('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('do_list');
    }
}
