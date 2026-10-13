<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adopt existing tables without changing or deleting their records.
        if (! Schema::hasTable('menu_items')) {
            Schema::create('menu_items', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->decimal('price', 10, 2);
            });
        }
        if (! Schema::hasTable('restaurant_tables')) {
            Schema::create('restaurant_tables', function (Blueprint $table) {
                $table->id();
                $table->string('table_number', 20)->unique();
                $table->unsignedInteger('capacity');
            });
        }
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('customer_name', 100);
                $table->foreignId('menu_item_id')->constrained('menu_items');
                $table->foreignId('table_id')->nullable()->constrained('restaurant_tables');
                $table->unsignedInteger('quantity');
                $table->decimal('total_amount', 14, 2);
                $table->string('status', 20)->default('Pending');
                $table->timestamp('created_at')->useCurrent();
            });
        }
        if (! Schema::hasTable('reservations')) {
            Schema::create('reservations', function (Blueprint $table) {
                $table->id();
                $table->string('customer_name', 100);
                $table->string('phone', 30);
                $table->foreignId('table_id')->constrained('restaurant_tables');
                $table->date('reservation_date');
                $table->time('reservation_time');
                $table->unsignedInteger('guests');
                $table->string('status', 20)->default('Pending');
                $table->timestamp('created_at')->useCurrent();
            });
        }
        if (! Schema::hasTable('inventory_items')) {
            Schema::create('inventory_items', function (Blueprint $table) {
                $table->id();
                $table->string('item_name', 100);
                $table->decimal('quantity', 10, 2);
                $table->string('unit', 20);
                $table->decimal('reorder_level', 10, 2);
            });
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Restaurant tables are preserved. Restore a database backup to undo adoption.');
    }
};
