<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('execution_details', function (Blueprint $table) {
            $table->foreignId('article_id')->nullable()->after('besoin_id')->constrained('articles')->nullOnDelete();
            $table->string('fournisseur')->nullable()->after('montant');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('execution_details', function (Blueprint $table) {
            $table->dropForeign(['article_id']);
            $table->dropColumn('article_id');
            $table->dropColumn('fournisseur');
        });
    }
};
