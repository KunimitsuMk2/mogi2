<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('items', function (Blueprint $table) {
        $table->id();                              // 商品ID（自動採番）
        $table->string('name');                    // 商品名
        $table->text('description');               // 商品説明
        $table->integer('price');                  // 価格
        $table->string('image_url');               // 商品画像のパス
        $table->string('condition');               // コンディション
        $table->foreignId('seller_id')             // 出品者のID
              ->constrained('users')               // usersテーブルと紐付け
              ->onDelete('cascade');               // ユーザー削除時に商品も削除
        $table->enum('status', ['available', 'sold'])->default('available'); // 商品状態
        $table->timestamps();                      // created_at, updated_at
     });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
