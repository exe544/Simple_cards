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
        Schema::table('columns', function (Blueprint $table) {
            $table->unsignedBigInteger('board_id')->after('place');

            $table->foreign('board_id')
                ->references('id')
                ->on('boards')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('columns', function (Blueprint $table) {
            $table->dropForeign('columns_board_id_foreign');
            $table->dropColumn('board_id');
        });;
    }
};
