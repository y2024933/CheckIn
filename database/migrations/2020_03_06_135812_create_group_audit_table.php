<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupAuditTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_audit', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mapping');
            $table->string('method', 20)->collation('utf8_general_ci');
            $table->text('para')->collation('utf8_general_ci');
            $table->string('updateuser', 20)->collation('utf8_general_ci');
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
        Schema::dropIfExists('group_audit');
    }
}
