<?php

namespace App\Http\Controllers;

use App\Services\RestaurantRecords;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RestaurantController extends Controller
{
    public function index()
    {
        return Inertia::render('Restaurant');
    }

    public function dashboard(Request $request, RestaurantRecords $records)
    {
        return $records->dashboard($request);
    }

    public function lookups(RestaurantRecords $records)
    {
        return $records->lookups();
    }
}
