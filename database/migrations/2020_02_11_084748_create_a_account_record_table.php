<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAAccountRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_record', function (Blueprint $table) {
            $table->increments('id');
            $table->string('accountid', 20);
            $table->enum('type', ['1','2'])->comment('1:上班 2:下班');
            $table->enum('status', ['0', '1']);
            $table->date('date');
            $table->timestamp('datetime');
            $table->index(['accountid', 'type', 'date'], 'accountid_type_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_record');
    }
}
