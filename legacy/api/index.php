<?php

declare(strict_types=1);

use App\Core\Database;
use App\Models\RestaurantManager;

header('Content-Type: application/json; charset=utf-8');

try {
    // Load the settings, connect to MySQL, then create the model object.
    $config = require dirname(__DIR__) . '/bootstrap.php';
    $db = (new Database($config['database']))->connect();
    $manager = new RestaurantManager($db);

    $module = $_REQUEST['module'] ?? 'dashboard';
    $action = $_REQUEST['action'] ?? 'list';
    $search = trim((string) ($_GET['search'] ?? ''));
    $id = (int) ($_REQUEST['id'] ?? 0);

    // The API chooses a method based on the module and action sent by AJAX.
    if ($module === 'dashboard') {
        success($manager->getDashboard());
    }

    if ($module === 'lookups') {
        success($manager->getLookups());
    }

    if ($module === 'orders') {
        if ($action === 'list') success($manager->getOrders($search));
        if ($action === 'save') {
            $manager->saveOrder($_POST);
            success(null, 'Order saved successfully.');
        }
        if ($action === 'delete') {
            $manager->deleteOrder($id);
            success(null, 'Order deleted successfully.');
        }
    }

    if ($module === 'reservations') {
        if ($action === 'list') success($manager->getReservations($search));
        if ($action === 'save') {
            $manager->saveReservation($_POST);
            success(null, 'Reservation saved successfully.');
        }
        if ($action === 'delete') {
            $manager->deleteReservation($id);
            success(null, 'Reservation deleted successfully.');
        }
    }

    if ($module === 'inventory') {
        if ($action === 'list') success($manager->getInventory($search));
        if ($action === 'save') {
            $manager->saveInventory($_POST);
            success(null, 'Inventory item saved successfully.');
        }
        if ($action === 'delete') {
            $manager->deleteInventory($id);
            success(null, 'Inventory item deleted successfully.');
        }
    }

    throw new RuntimeException('Invalid request.');
} catch (Throwable $error) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $error->getMessage()]);
}

// All successful API responses use the same JSON format.
function success(mixed $data = null, string $message = ''): never
{
    echo json_encode(['success' => true, 'data' => $data, 'message' => $message]);
    exit;
}
