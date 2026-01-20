<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('executions', function (Blueprint $table) {
            $table->foreignId('besoin_id')->nullable()->after('ligne_budget_id')->constrained('besoins')->nullOnDelete();
            $table->integer('quantite')->default(1)->after('besoin_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('executions', function (Blueprint $table) {
            $table->dropForeign(['besoin_id']);
            $table->dropColumn(['besoin_id', 'quantite']);
        });
    }
};
