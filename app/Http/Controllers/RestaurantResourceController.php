<?php

namespace App\Http\Controllers;

use App\Services\RestaurantRecords;
use Illuminate\Http\Request;
use Inertia\Inertia;

abstract class RestaurantResourceController extends Controller
{
    protected string $module;

    public function __construct(protected RestaurantRecords $records) {}

    public function index(Request $request)
    {
        $this->records->authorizeModule($request, $this->module);

        return $request->is('api/*') ? $this->records->index($request, $this->module) : Inertia::render('Restaurant', ['initialPage' => $this->module]);
    }

    public function create(Request $request)
    {
        $this->records->authorizeModule($request, $this->module);
        abort_unless($request->user()->can('records.create') || ($request->user()->isCustomer() && $this->module !== 'inventory'), 403);

        return Inertia::render('Restaurant', ['initialPage' => $this->module, 'initialEditor' => ['module' => $this->module, 'record' => null]]);
    }

    protected function record(Request $request, int $id)
    {
        // Reuse the scoped listing so direct links cannot expose another account's record.
        $response = $this->records->index($request, $this->module, $id)->getData(true);
        $record = collect($response['data'])->firstWhere('id', $id);
        abort_unless($record, 404);

        return $record;
    }

    public function show(Request $request, int $id)
    {
        $record = $this->record($request, $id);

        return $request->is('api/*') ? response()->json(['data' => $record]) : Inertia::render('RecordShow', ['module' => $this->module, 'record' => $record]);
    }

    public function edit(Request $request, int $id)
    {
        abort_unless($request->user()->can('records.update') && ! $request->user()->isCustomer(), 403);

        return Inertia::render('Restaurant', ['initialPage' => $this->module, 'initialEditor' => ['module' => $this->module, 'record' => $this->record($request, $id)]]);
    }

    public function store(Request $request)
    {
        return $this->records->store($request, $this->module);
    }

    public function update(Request $request, int $id)
    {
        return $this->records->update($request, $this->module, $id);
    }

    public function destroy(Request $request, int $id)
    {
        return $this->records->destroy($request, $this->module, $id);
    }
}
