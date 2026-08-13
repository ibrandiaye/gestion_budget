<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('engagements', function (Blueprint $table) {
            $table->unsignedBigInteger('commande_id')->nullable()->change();
            
            $table->unsignedBigInteger('compte_id')->nullable()->after('commande_id');
            $table->unsignedBigInteger('service_id')->nullable()->after('compte_id');
            $table->unsignedBigInteger('budget_id')->nullable()->after('service_id');
            
            $table->foreign('compte_id')->references('id')->on('comptes')->nullOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();
            $table->foreign('budget_id')->references('id')->on('budgets')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('engagements', function (Blueprint $table) {
            $table->dropForeign(['compte_id']);
            $table->dropForeign(['service_id']);
            $table->dropForeign(['budget_id']);
            
            $table->dropColumn(['compte_id', 'service_id', 'budget_id']);
            
            $table->unsignedBigInteger('commande_id')->nullable(false)->change();
        });
    }
};
