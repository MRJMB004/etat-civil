<?php
// database/migrations/2024_01_01_000000_add_extra_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->enum('role', [
                'admin',
                'agent_registre', 
                'agent_saisie',
                'consultant',
                'guest'
            ])->default('guest')->after('avatar');
            $table->enum('status', [
                'active',
                'inactive', 
                'suspended',
                'pending'
            ])->default('pending')->after('role');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->foreignId('region_id')->nullable()->constrained('regions')->after('last_login_ip');
            $table->foreignId('district_id')->nullable()->constrained('districts')->after('region_id');
            $table->foreignId('commune_id')->nullable()->constrained('communes')->after('district_id');
            $table->foreignId('fokontany_id')->nullable()->constrained('fokontany')->after('commune_id');
            $table->json('settings')->nullable()->after('fokontany_id');
            
            // Index pour les performances
            $table->index(['role', 'status']);
            $table->index('last_login_at');
            $table->index(['region_id', 'district_id', 'commune_id']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'phone', 'avatar', 'role', 'status',
                'last_login_at', 'last_login_ip', 'region_id', 
                'district_id', 'commune_id', 'fokontany_id', 'settings'
            ]);
        });
    }
};