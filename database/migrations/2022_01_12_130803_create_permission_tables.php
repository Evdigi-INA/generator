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
        $teams = config(key: 'permission.teams');
        $tableNames = config(key: 'permission.table_names');
        $columnNames = config(key: 'permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        if (empty($tableNames)) {
            throw new \Exception(message: 'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }
        if ($teams && empty($columnNames['team_foreign_key'] ?? null)) {
            throw new \Exception(message: 'Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create(table: $tableNames['permissions'], callback: static function (Blueprint $table): void {
            // $table->engine('InnoDB');
            $table->bigIncrements(column: 'id'); // permission id
            $table->string(column: 'name');       // For MyISAM use string('name', 225); // (or 166 for InnoDB with Redundant/Compact row format)
            $table->string(column: 'guard_name'); // For MyISAM use string('guard_name', 25);
            $table->timestamps();

            $table->unique(columns: ['name', 'guard_name']);
        });

        Schema::create(table: $tableNames['roles'], callback: static function (Blueprint $table) use ($teams, $columnNames): void {
            // $table->engine('InnoDB');
            $table->bigIncrements(column: 'id'); // role id
            if ($teams || config(key: 'permission.testing')) { // permission.testing is a fix for sqlite testing
                $table->unsignedBigInteger(column: $columnNames['team_foreign_key'])->nullable();
                $table->index(columns: $columnNames['team_foreign_key'], name: 'roles_team_foreign_key_index');
            }
            $table->string(column: 'name');       // For MyISAM use string('name', 225); // (or 166 for InnoDB with Redundant/Compact row format)
            $table->string(column: 'guard_name'); // For MyISAM use string('guard_name', 25);
            $table->timestamps();
            if ($teams || config(key: 'permission.testing')) {
                $table->unique(columns: [$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(columns: ['name', 'guard_name']);
            }
        });

        Schema::create(table: $tableNames['model_has_permissions'], callback: static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams): void {
            $table->unsignedBigInteger(column: $pivotPermission);

            $table->string(column: 'model_type');
            $table->unsignedBigInteger(column: $columnNames['model_morph_key']);
            $table->index(columns: [$columnNames['model_morph_key'], 'model_type'], name: 'model_has_permissions_model_id_model_type_index');

            $table->foreign(columns: $pivotPermission)
                ->references(columns: 'id') // permission id
                ->on(table: $tableNames['permissions'])
                ->onDelete(action: 'cascade');
            if ($teams) {
                $table->unsignedBigInteger(column: $columnNames['team_foreign_key']);
                $table->index(columns: $columnNames['team_foreign_key'], name: 'model_has_permissions_team_foreign_key_index');

                $table->primary(columns: [$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    name: 'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary(columns: [$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    name: 'model_has_permissions_permission_model_type_primary');
            }

        });

        Schema::create(table: $tableNames['model_has_roles'], callback: static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams): void {
            $table->unsignedBigInteger(column: $pivotRole);

            $table->string(column: 'model_type');
            $table->unsignedBigInteger(column: $columnNames['model_morph_key']);
            $table->index(columns: [$columnNames['model_morph_key'], 'model_type'], name: 'model_has_roles_model_id_model_type_index');

            $table->foreign(columns: $pivotRole)
                ->references(columns: 'id') // role id
                ->on(table: $tableNames['roles'])
                ->onDelete(action: 'cascade');
            if ($teams) {
                $table->unsignedBigInteger(column: $columnNames['team_foreign_key']);
                $table->index(columns: $columnNames['team_foreign_key'], name: 'model_has_roles_team_foreign_key_index');

                $table->primary(columns: [$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    name: 'model_has_roles_role_model_type_primary');
            } else {
                $table->primary(columns: [$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    name: 'model_has_roles_role_model_type_primary');
            }
        });

        Schema::create(table: $tableNames['role_has_permissions'], callback: static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission): void {
            $table->unsignedBigInteger(column: $pivotPermission);
            $table->unsignedBigInteger(column: $pivotRole);

            $table->foreign(columns: $pivotPermission)
                ->references(columns: 'id') // permission id
                ->on(table: $tableNames['permissions'])
                ->onDelete(action: 'cascade');

            $table->foreign(columns: $pivotRole)
                ->references(columns: 'id') // role id
                ->on(table: $tableNames['roles'])
                ->onDelete(action: 'cascade');

            $table->primary(columns: [$pivotPermission, $pivotRole], name: 'role_has_permissions_permission_id_role_id_primary');
        });

        app(abstract: 'cache')
            ->store(name: config(key: 'permission.cache.store') != 'default' ? config(key: 'permission.cache.store') : null)
            ->forget(key: config(key: 'permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config(key: 'permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception(message: 'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::drop(table: $tableNames['role_has_permissions']);
        Schema::drop(table: $tableNames['model_has_roles']);
        Schema::drop(table: $tableNames['model_has_permissions']);
        Schema::drop(table: $tableNames['roles']);
        Schema::drop(table: $tableNames['permissions']);
    }
};
