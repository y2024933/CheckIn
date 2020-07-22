<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountRemarkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_remark', function (Blueprint $table) {
            $table->increments('id');
            $table->string('accountid', 20)->collation('utf8_general_ci');
            $table->enum('type', ['1', '2'])->collation('utf8_general_ci');
            $table->text('remark')->collation('utf8_general_ci')->nullable();
            $table->date('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_remark');
    }
}
