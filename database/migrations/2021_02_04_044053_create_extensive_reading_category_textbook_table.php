<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtensiveReadingCategoryTextbookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extensive_reading_category_textbook', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('extensive_reading_category_id')->unsigned();
            $table->bigInteger('textbook_id')->unsigned();
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
        Schema::dropIfExists('extensive_reading_category_textbook');
    }
}
