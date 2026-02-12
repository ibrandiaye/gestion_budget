<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Add compte_parent_id
        Schema::table('comptes', function (Blueprint $table) {
            $table->foreignId('compte_parent_id')->nullable()->constrained('compte_parents')->nullOnDelete();
        });

        // 2. Seed default parents
        $parents = [
            ['numero' => '10', 'libelle' => 'FONCTIONNEMENT', 'type' => 'fonctionnement'],
            ['numero' => '20', 'libelle' => 'INVESTISSEMENT', 'type' => 'investissement'],
        ];

        foreach ($parents as $p) {
             DB::table('compte_parents')->insert([
                'numero' => $p['numero'],
                'libelle' => $p['libelle'],
                'type' => $p['type'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Migrate existing accounts
        $fonctionnementParent = DB::table('compte_parents')->where('type', 'fonctionnement')->first();
        $investissementParent = DB::table('compte_parents')->where('type', 'investissement')->first();

        if ($fonctionnementParent) {
            DB::table('comptes')->where('type', 'fonctionnement')->update(['compte_parent_id' => $fonctionnementParent->id]);
        }
        if ($investissementParent) {
            DB::table('comptes')->where('type', 'investissement')->update(['compte_parent_id' => $investissementParent->id]);
        }

        // 4. Drop columns
        Schema::table('comptes', function (Blueprint $table) {
             $table->dropColumn('type');
             $table->dropForeign(['parent_id']);
             $table->dropColumn('parent_id');
        });
    }

    public function down()
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->string('type')->nullable(); 
            $table->foreignId('parent_id')->nullable()->constrained('comptes');
            $table->dropForeign(['compte_parent_id']);
            $table->dropColumn('compte_parent_id');
        });
    }
};
