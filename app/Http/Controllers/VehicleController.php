<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    /**
     * List all vehicles.
     */
    public function index(): View
    {
        return view('admin.vehicles.index', [
            'vehicles' => Vehicle::withCount('shipments')->latest()->paginate(20),
        ]);
    }

    /**
     * Show the create vehicle form.
     */
    public function create(): View
    {
        return view('admin.vehicles.form', [
            'vehicle' => new Vehicle(),
            'types' => Vehicle::TYPES,
            'statuses' => Vehicle::STATUSES,
        ]);
    }

    /**
     * Store a new vehicle.
     */
    public function store(Request $request): RedirectResponse
    {
        $vehicle = Vehicle::create($this->validated($request));

        return redirect()->route('admin.vehicles.index')->with('status', "Vehicle {$vehicle->name} added.");
    }

    /**
     * Show the edit vehicle form.
     */
    public function edit(Vehicle $vehicle): View
    {
        return view('admin.vehicles.form', [
            'vehicle' => $vehicle,
            'types' => Vehicle::TYPES,
            'statuses' => Vehicle::STATUSES,
        ]);
    }

    /**
     * Update a vehicle.
     */
    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($this->validated($request));

        return redirect()->route('admin.vehicles.index')->with('status', 'Vehicle updated.');
    }

    /**
     * Delete a vehicle.
     */
    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return back()->with('status', 'Vehicle removed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:' . implode(',', Vehicle::TYPES)],
            'plate_number' => ['nullable', 'string', 'max:20'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'driver_phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', 'in:' . implode(',', Vehicle::STATUSES)],
        ]);
    }
}
