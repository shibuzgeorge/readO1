<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForeignKeysForModuleTextbookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('module_textbook', function (Blueprint $table) {
            $table->foreign('textbook_id')->references('id')->on('textbooks')->onDelete('cascade');
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('module_textbook', function(Blueprint $table){
            $table->dropForeign('module_textbook_textbook_id_foreign');
            $table->dropForeign('module_textbook_module_id_foreign');
        });
    }
}
