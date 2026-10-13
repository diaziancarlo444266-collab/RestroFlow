<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RestaurantRecords
{
    private function model(string $module): string
    {
        return match ($module) {
            'orders' => Order::class, 'reservations' => Reservation::class, 'inventory' => InventoryItem::class,
        };
    }

    public function dashboard(Request $request)
    {
        if (! $request->user()->hasRole('Admin')) {
            return response()->json(['data' => ['orders' => Order::where('user_id', $request->user()->id)->count(), 'reservations' => Reservation::where('user_id', $request->user()->id)->count()]]);
        }

        return response()->json(['data' => [
            'orders' => Order::count(), 'reservations' => Reservation::count(), 'inventory' => InventoryItem::count(),
            'low_stock' => InventoryItem::whereColumn('quantity', '<=', 'reorder_level')->count(),
        ]]);
    }

    public function lookups()
    {
        return response()->json(['data' => [
            'menu' => MenuItem::orderBy('name')->get(),
            'tables' => RestaurantTable::orderBy('table_number')->get(),
        ]]);
    }

    public function index(Request $request, string $module, ?int $id = null)
    {
        $this->authorizeModule($request, $module);
        $request->validate(['search' => 'nullable|string|max:255']);
        $search = '%'.$request->string('search')->trim().'%';
        $query = match ($module) {
            'orders' => Order::with(['menuItem', 'table'])
                ->where(fn ($q) => $q->where('customer_name', 'like', $search)->orWhereHas('menuItem', fn ($menu) => $menu->where('name', 'like', $search)))
                ->orderByDesc('id'),
            'reservations' => Reservation::with('table')
                ->where(fn ($q) => $q->where('customer_name', 'like', $search)->orWhere('phone', 'like', $search))
                ->orderByDesc('reservation_date')->orderByDesc('reservation_time')->orderByDesc('id'),
            'inventory' => InventoryItem::where('item_name', 'like', $search)->orderByDesc('id'),
        };

        if (in_array($module, ['orders', 'reservations'], true) && ! $request->user()->hasRole('Admin')) {
            $query->where($module.'.user_id', $request->user()->id);
        }

        if ($id !== null) {
            $query->whereKey($id);
        }
        $records = $query->get()->map(function ($record) use ($module) {
            $data = $record->attributesToArray();
            if ($module === 'orders') {
                $data['menu_name'] = $record->menuItem?->name;
            }
            if ($module !== 'inventory') {
                $data['table_number'] = $record->table?->table_number;
            }

            return $data;
        });

        return response()->json(['data' => $records]);
    }

    public function authorizeModule(Request $request, string $module): void
    {
        abort_if($module === 'reservations' && ! $request->user()->hasRole('Admin') && ! $request->user()->isCustomer(), 403);
        abort_if($module === 'inventory' && ! $request->user()->hasAnyRole(['Admin', 'Staff']), 403);
    }

    private function validated(Request $request, string $module): array
    {
        $rules = match ($module) {
            'orders' => [
                'customer_name' => 'required|string|max:100', 'menu_item_id' => 'required|integer|exists:menu_items,id',
                'table_id' => 'nullable|integer|exists:restaurant_tables,id', 'quantity' => 'required|integer|min:1|max:10000',
                'status' => ['required', Rule::in(['Pending', 'Preparing', 'Completed', 'Cancelled'])],
            ],
            'reservations' => [
                'customer_name' => 'required|string|max:100', 'phone' => 'required|string|max:30',
                'table_id' => 'required|integer|exists:restaurant_tables,id',
                'reservation_date' => 'required|date_format:Y-m-d', 'reservation_time' => 'required|date_format:H:i',
                'guests' => 'required|integer|min:1|max:10000',
                'status' => ['required', Rule::in(['Pending', 'Confirmed', 'Completed', 'Cancelled'])],
            ],
            'inventory' => [
                'item_name' => 'required|string|max:100', 'unit' => 'required|string|max:20',
                'quantity' => 'required|numeric|min:0|max:999999.99|decimal:0,2',
                'reorder_level' => 'required|numeric|min:0|max:999999.99|decimal:0,2',
            ],
        };
        $data = $request->validate($rules);
        if ($module === 'orders') {
            // Only the stored menu price determines the total.
            $price = DB::table('menu_items')->where('id', $data['menu_item_id'])->value('price');
            $data['total_amount'] = round((float) $price * 100) * $data['quantity'] / 100;
            $data['table_id'] = $data['table_id'] ?? null;
        }

        return $data;
    }

    public function store(Request $request, string $module)
    {
        $this->authorizeModule($request, $module);
        $customerRecord = in_array($module, ['orders', 'reservations'], true) && $request->user()->isCustomer();
        abort_unless($customerRecord || $request->user()->can('records.create'), 403);
        if ($customerRecord) {
            $request->merge(['customer_name' => mb_substr($request->user()->name, 0, 100), 'status' => 'Pending']);
        }
        $record = new ($this->model($module))($this->validated($request, $module));
        if (in_array($module, ['orders', 'reservations'], true)) {
            $record->user_id = $request->user()->id;
        }
        $record->save();

        return response()->json(['data' => $record, 'message' => 'Record saved successfully.'], 201);
    }

    public function update(Request $request, string $module, int $id)
    {
        $this->authorizeModule($request, $module);
        abort_if($request->user()->isCustomer(), 403);
        $record = ($this->model($module))::findOrFail($id);
        abort_if($module === 'orders' && ! $request->user()->hasRole('Admin') && $record->user_id !== $request->user()->id, 403);
        $record->update($this->validated($request, $module));

        return response()->json(['data' => $record, 'message' => 'Record updated successfully.']);
    }

    public function destroy(Request $request, string $module, int $id)
    {
        abort_unless($request->user()->hasRole('Admin'), 403);
        ($this->model($module))::findOrFail($id)->delete();

        return response()->json(['message' => 'Record deleted successfully.']);
    }
}
