<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use RuntimeException;

/*
 * This is the main model of the project.
 * It contains the database operations for orders, reservations and inventory.
 */
class RestaurantManager
{
    public function __construct(private PDO $db)
    {
    }

    // -------------------- DASHBOARD --------------------

    public function getDashboard(): array
    {
        return [
            'orders' => (int) $this->db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'reservations' => (int) $this->db->query('SELECT COUNT(*) FROM reservations')->fetchColumn(),
            'inventory' => (int) $this->db->query('SELECT COUNT(*) FROM inventory_items')->fetchColumn(),
            'low_stock' => (int) $this->db->query(
                'SELECT COUNT(*) FROM inventory_items WHERE quantity <= reorder_level'
            )->fetchColumn(),
        ];
    }

    // These records are used in the order and reservation dropdowns.
    public function getLookups(): array
    {
        return [
            'menu' => $this->db->query('SELECT * FROM menu_items ORDER BY name')->fetchAll(),
            'tables' => $this->db->query('SELECT * FROM restaurant_tables ORDER BY table_number')->fetchAll(),
        ];
    }

    // -------------------- ORDERS CRUD --------------------

    public function getOrders(string $search = ''): array
    {
        $sql = 'SELECT o.*, m.name AS menu_name, t.table_number
                FROM orders o
                INNER JOIN menu_items m ON m.id = o.menu_item_id
                LEFT JOIN restaurant_tables t ON t.id = o.table_id
                WHERE o.customer_name LIKE :search OR m.name LIKE :search
                ORDER BY o.id DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['search' => '%' . $search . '%']);
        return $statement->fetchAll();
    }

    public function saveOrder(array $data): void
    {
        $menuId = (int) ($data['menu_item_id'] ?? 0);
        $quantity = max(1, (int) ($data['quantity'] ?? 1));

        // The price comes from MySQL so users cannot manually change the total.
        $priceStatement = $this->db->prepare('SELECT price FROM menu_items WHERE id = :id');
        $priceStatement->execute(['id' => $menuId]);
        $price = $priceStatement->fetchColumn();
        if ($price === false) {
            throw new RuntimeException('Please select a valid menu item.');
        }

        $values = [
            'customer_name' => trim((string) ($data['customer_name'] ?? '')),
            'menu_item_id' => $menuId,
            'table_id' => !empty($data['table_id']) ? (int) $data['table_id'] : null,
            'quantity' => $quantity,
            'total_amount' => (float) $price * $quantity,
            'status' => $data['status'] ?? 'Pending',
        ];

        if ($values['customer_name'] === '') {
            throw new RuntimeException('Customer name is required.');
        }

        if (!empty($data['id'])) {
            $values['id'] = (int) $data['id'];
            $sql = 'UPDATE orders SET customer_name=:customer_name, menu_item_id=:menu_item_id,
                    table_id=:table_id, quantity=:quantity, total_amount=:total_amount,
                    status=:status WHERE id=:id';
        } else {
            $sql = 'INSERT INTO orders (customer_name, menu_item_id, table_id, quantity, total_amount, status)
                    VALUES (:customer_name, :menu_item_id, :table_id, :quantity, :total_amount, :status)';
        }

        $this->db->prepare($sql)->execute($values);
    }

    public function deleteOrder(int $id): void
    {
        $this->deleteRecord('orders', $id);
    }

    // -------------------- RESERVATIONS CRUD --------------------

    public function getReservations(string $search = ''): array
    {
        $sql = 'SELECT r.*, t.table_number
                FROM reservations r
                INNER JOIN restaurant_tables t ON t.id = r.table_id
                WHERE r.customer_name LIKE :search OR r.phone LIKE :search
                ORDER BY r.reservation_date DESC, r.reservation_time DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['search' => '%' . $search . '%']);
        return $statement->fetchAll();
    }

    public function saveReservation(array $data): void
    {
        $values = [
            'customer_name' => trim((string) ($data['customer_name'] ?? '')),
            'phone' => trim((string) ($data['phone'] ?? '')),
            'table_id' => (int) ($data['table_id'] ?? 0),
            'reservation_date' => $data['reservation_date'] ?? '',
            'reservation_time' => $data['reservation_time'] ?? '',
            'guests' => max(1, (int) ($data['guests'] ?? 1)),
            'status' => $data['status'] ?? 'Pending',
        ];

        if ($values['customer_name'] === '' || $values['phone'] === '') {
            throw new RuntimeException('Customer name and phone are required.');
        }

        if (!empty($data['id'])) {
            $values['id'] = (int) $data['id'];
            $sql = 'UPDATE reservations SET customer_name=:customer_name, phone=:phone,
                    table_id=:table_id, reservation_date=:reservation_date,
                    reservation_time=:reservation_time, guests=:guests, status=:status WHERE id=:id';
        } else {
            $sql = 'INSERT INTO reservations
                    (customer_name, phone, table_id, reservation_date, reservation_time, guests, status)
                    VALUES (:customer_name, :phone, :table_id, :reservation_date, :reservation_time, :guests, :status)';
        }

        $this->db->prepare($sql)->execute($values);
    }

    public function deleteReservation(int $id): void
    {
        $this->deleteRecord('reservations', $id);
    }

    // -------------------- INVENTORY CRUD --------------------

    public function getInventory(string $search = ''): array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM inventory_items WHERE item_name LIKE :search ORDER BY id DESC'
        );
        $statement->execute(['search' => '%' . $search . '%']);
        return $statement->fetchAll();
    }

    public function saveInventory(array $data): void
    {
        $values = [
            'item_name' => trim((string) ($data['item_name'] ?? '')),
            'quantity' => max(0, (float) ($data['quantity'] ?? 0)),
            'unit' => trim((string) ($data['unit'] ?? '')),
            'reorder_level' => max(0, (float) ($data['reorder_level'] ?? 0)),
        ];

        if ($values['item_name'] === '' || $values['unit'] === '') {
            throw new RuntimeException('Item name and unit are required.');
        }

        if (!empty($data['id'])) {
            $values['id'] = (int) $data['id'];
            $sql = 'UPDATE inventory_items SET item_name=:item_name, quantity=:quantity,
                    unit=:unit, reorder_level=:reorder_level WHERE id=:id';
        } else {
            $sql = 'INSERT INTO inventory_items (item_name, quantity, unit, reorder_level)
                    VALUES (:item_name, :quantity, :unit, :reorder_level)';
        }

        $this->db->prepare($sql)->execute($values);
    }

    public function deleteInventory(int $id): void
    {
        $this->deleteRecord('inventory_items', $id);
    }

    // A small reusable method for the delete part of CRUD.
    private function deleteRecord(string $table, int $id): void
    {
        $statement = $this->db->prepare("DELETE FROM {$table} WHERE id = :id");
        $statement->execute(['id' => $id]);
    }
}
