<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(table: 'users', callback: function (Blueprint $table): void {
            $table->text(column: 'two_factor_secret')
                ->after(column: 'password')
                ->nullable();

            $table->text(column: 'two_factor_recovery_codes')
                ->after(column: 'two_factor_secret')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(table: 'users', callback: function (Blueprint $table): void {
            $table->dropColumn(columns: ['two_factor_secret', 'two_factor_recovery_codes']);
        });
    }
};
