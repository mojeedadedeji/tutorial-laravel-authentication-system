<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\ShipmentStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Admin dashboard with platform-wide stats.
     */
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'shipments' => Shipment::count(),
                'revenue' => Shipment::sum('amount'),
                'in_transit' => Shipment::where('status', 'in_transit')->count(),
                'delivered' => Shipment::where('status', 'delivered')->count(),
                'vehicles' => \App\Models\Vehicle::count(),
                'posts' => \App\Models\Post::count(),
            ],
            'recentShipments' => Shipment::with('user')->latest()->limit(10)->get(),
        ]);
    }

    /**
     * List all shipments across all users.
     */
    public function shipments(Request $request): View
    {
        $shipments = Shipment::with('user')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->q, fn ($q) => $q->where('reference', 'like', '%' . $request->q . '%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.shipments', [
            'shipments' => $shipments,
            'statuses' => Shipment::STATUSES,
            'vehicles' => Vehicle::orderBy('name')->get(),
        ]);
    }

    /**
     * Update any shipment's status (admin).
     */
    public function updateShipmentStatus(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', Shipment::STATUSES)],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
        ]);

        $oldStatus = $shipment->status;

        $shipment->update([
            'status' => $validated['status'],
            'vehicle_id' => $validated['vehicle_id'] ?? $shipment->vehicle_id,
            'delivered_at' => $validated['status'] === 'delivered' && ! $shipment->delivered_at
                ? now()
                : $shipment->delivered_at,
        ]);

        if ($oldStatus !== $shipment->status) {
            $shipment->user->notify(new ShipmentStatusUpdated($shipment));
        }

        return back()->with('status', 'Shipment status updated.');
    }

    /**
     * List all users.
     */
    public function users(Request $request): View
    {
        $users = User::withCount(['shipments', 'paymentMethods'])
            ->when($request->q, fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%' . $request->q . '%')->orWhere('email', 'like', '%' . $request->q . '%')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', ['users' => $users]);
    }

    /**
     * Toggle a user's admin flag.
     */
    public function toggleAdmin(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot change your own admin status.');

        $user->update(['is_admin' => ! $user->is_admin]);

        return back()->with('status', 'User admin status updated.');
    }
}
