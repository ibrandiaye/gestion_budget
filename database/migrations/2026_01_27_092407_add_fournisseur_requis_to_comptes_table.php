<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->boolean('fournisseur_requis')->default(true)->after('is_marche');
        });
    }

    public function down()
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropColumn('fournisseur_requis');
        });
    }
};
