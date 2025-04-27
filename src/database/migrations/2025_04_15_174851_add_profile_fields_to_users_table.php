<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // すでにavatarは$fillableにあるので、存在確認
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('email');
            }
            $table->string('postal_code')->nullable()->after('avatar');
            $table->string('address')->nullable()->after('postal_code');
            $table->string('building_name')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // avatarはすでに存在している可能性があるので削除しない
            $table->dropColumn(['postal_code', 'address', 'building_name']);
        });
    }
}