<?php
    
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique();
            $table->string('password');
            $table->string('email')->unique();
            $table->string('name');
            $table->string('read_name');
            $table->string('status');
            $table->date('birthday'); // This creates the birthday column with date data type
            $table->string('phone_number')->nullable();
            $table->string('memo')->nullable();
            $table->enum('phone_device', ['android', 'iphone'])->nullable();
            $table->integer('ninetieth_life');
            $table->integer('work_life');
            $table->integer('die_life');
            $table->integer('healthy_life');
            $table->integer('average_life');
            $table->json('common1_permission')->nullable();
            $table->json('mygroup_permission')->nullable();
            $table->string('group_id')->nullable();
            $table->string('avatar')->nullable();
            $table->json('view_groups')->nullable();
            $table->rememberToken();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
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
        Schema::dropIfExists('users');
    }
}
