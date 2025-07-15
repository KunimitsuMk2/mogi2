<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRatingsTable extends Migration
{
    public function up()
    {
        Schema::create('user_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('rater_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rated_user_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating')->unsigned()->comment('1-5の評価');
            $table->text('comment')->nullable();
            $table->timestamps();
            
            // 同じ取引で同じユーザーが複数回評価しないように制約
            $table->unique(['transaction_id', 'rater_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_ratings');
    }
}