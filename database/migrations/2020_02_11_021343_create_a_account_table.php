<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account', function (Blueprint $table) {
            $table->comment = '会员帐号';
            $table->increments('accountid');
            $table->string('accountcode', 20)->collation('utf8_general_ci')->nullable();
            $table->string('nickname', 20)->collation('utf8_general_ci')->nullable();
            $table->string('password', 255)->collation('utf8_general_ci');
            $table->enum('status', ['-1','0', '1','2','3']);
            $table->timestamp('lastlogin')->nullable();
            // $table->mediumInteger('lastupdateduser');
            // $table->timestamp('udate')->default('0000-00-00 00:00:00');
            $table->timestamp('cdate')->default(DB::raw('CURRENT_TIMESTAMP(0)'));
            $table->unique(['nickname'], 'nickname');
            $table->unique(['accountcode'], 'accountcode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account');
    }
}
