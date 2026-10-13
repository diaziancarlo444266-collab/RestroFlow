<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['menu_items', 'restaurant_tables', 'orders', 'reservations', 'inventory_items'] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                if (! Schema::hasColumn($name, 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['menu_items', 'restaurant_tables', 'orders', 'reservations', 'inventory_items'] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->dropColumn('updated_at');
                if (! in_array($name, ['orders', 'reservations'], true)) {
                    $table->dropColumn('created_at');
                }
            });
        }
    }
};
