<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForeignKeyForExtensiveReadingCategoryTextbookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('extensive_reading_category_textbook', function (Blueprint $table) {
            $table->foreign('extensive_reading_category_id', 'extensive_reading_category_textbook_erc_id_foreign')->references('id')->on('extensive_reading_categories')->onDelete('cascade');
            $table->foreign('textbook_id')->references('id')->on('textbooks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('extensive_reading_category_textbook', function(Blueprint $table){
            $table->dropForeign('extensive_reading_category_textbook_erc_id_foreign');
            $table->dropForeign('extensive_reading_category_textbook_textbook_id_foreign');
        });
    }
}
