<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users_boards', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id');
            $table->unsignedBigInteger('board_id')->after('user_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('board_id')->references('id')->on('boards')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_boards', function (Blueprint $table) {
            $table->dropForeign('users_boards_user_id_foreign');
            $table->dropForeign('users_boards_board_id_foreign');
            $table->dropColumn('user_id');
            $table->dropColumn('board_id');
        });
    }
};
