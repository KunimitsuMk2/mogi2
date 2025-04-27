<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('products', function (Blueprint $table) {
        $table->id(); // 自動ID
        $table->string('name'); // 商品名
        $table->text('description'); // 商品説明
        $table->string('brand_name')->nullable(); // ブランド名（任意）
        $table->integer('price'); // 価格
        $table->unsignedTinyInteger('condition'); // 商品状態（数値で管理予定）
        $table->string('image_path'); // 画像のパス
        $table->foreignId('seller_id')->constrained('users')->onDelete('cascade'); // 出品者（Userとのリレーション）
        $table->timestamps(); // created_at, updated_at
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
