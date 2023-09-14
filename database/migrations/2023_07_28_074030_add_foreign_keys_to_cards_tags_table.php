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
        Schema::table('cards_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('tag_id');

            $table->foreign('card_id')->references('id')->on('cards')->cascadeOnDelete();
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards_tags', function (Blueprint $table) {
            $table->dropForeign('cards_tags_card_id_foreign');
            $table->dropForeign('cards_tags_tag_id_foreign');
            $table->dropColumn('card_id');
            $table->dropColumn('tag_id');
        });
    }
};
