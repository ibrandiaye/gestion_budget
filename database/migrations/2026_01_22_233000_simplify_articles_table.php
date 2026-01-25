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
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['code', 'prix_unitaire', 'unite']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('code')->unique()->after('compte_id');
            $table->decimal('prix_unitaire', 15, 2)->after('designation');
            $table->string('unite')->default('unité')->after('prix_unitaire');
        });
    }
};
