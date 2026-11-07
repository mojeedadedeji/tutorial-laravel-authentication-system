<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the logistics dashboard with real aggregates.
     */
    public function index(): View
    {
        $userId = auth()->id();

        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $shipmentsThisMonth = Shipment::where('user_id', $userId)
            ->where('shipped_at', '>=', $monthStart)
            ->count();

        $shipmentsLastMonth = Shipment::where('user_id', $userId)
            ->whereBetween('shipped_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $revenueThisMonth = (float) Shipment::where('user_id', $userId)
            ->where('shipped_at', '>=', $monthStart)
            ->sum('amount');

        $revenueLastMonth = (float) Shipment::where('user_id', $userId)
            ->whereBetween('shipped_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');

        $onTimeRate = $this->onTimeRate($userId, $monthStart, now());
        $onTimeRateLastMonth = $this->onTimeRate($userId, $lastMonthStart, $lastMonthEnd);

        $inTransit = Shipment::where('user_id', $userId)->where('status', 'in_transit')->count();
        $delayed = Shipment::where('user_id', $userId)->where('status', 'delayed')->count();

        [$chartLabels, $chartShipments, $chartOnTime] = $this->monthlySeries($userId);

        $topLanes = Shipment::where('user_id', $userId)
            ->where('shipped_at', '>=', now()->subDays(90))
            ->select('origin', 'destination', DB::raw('count(*) as total'))
            ->groupBy('origin', 'destination')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $laneTotal = (clone $topLanes)->sum('total') ?: 1;

        return view('dashboard', [
            'kpis' => [
                'shipments' => $shipmentsThisMonth,
                'shipments_delta' => $this->percentChange($shipmentsThisMonth, $shipmentsLastMonth),
                'ontime' => $onTimeRate,
                'ontime_delta' => round($onTimeRate - $onTimeRateLastMonth, 1),
                'in_transit' => $inTransit,
                'delayed' => $delayed,
                'revenue' => $revenueThisMonth,
                'revenue_delta' => $this->percentChange($revenueThisMonth, $revenueLastMonth),
            ],
            'chartLabels' => $chartLabels,
            'chartShipments' => $chartShipments,
            'chartOnTime' => $chartOnTime,
            'topLanes' => $topLanes,
            'laneTotal' => $laneTotal,
        ]);
    }

    /**
     * On-time rate: delivered shipments as a share of completed ones.
     */
    private function onTimeRate(int $userId, $from, $to): float
    {
        $completed = Shipment::where('user_id', $userId)
            ->whereIn('status', ['delivered', 'delayed'])
            ->whereBetween('shipped_at', [$from, $to])
            ->count();

        if ($completed === 0) {
            return 0.0;
        }

        $delivered = Shipment::where('user_id', $userId)
            ->where('status', 'delivered')
            ->whereBetween('shipped_at', [$from, $to])
            ->count();

        return round($delivered / $completed * 100, 1);
    }

    /**
     * Monthly shipment counts and on-time rates for the trailing 12 months.
     *
     * @return array{0: array<int, string>, 1: array<int, int>, 2: array<int, float>}
     */
    private function monthlySeries(int $userId): array
    {
        $shipments = Shipment::where('user_id', $userId)
            ->where('shipped_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get(['shipped_at', 'status']);

        $byMonth = $shipments->groupBy(fn (Shipment $s) => $s->shipped_at->format('Y-m'));

        $labels = [];
        $counts = [];
        $onTime = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $monthShipments = $byMonth->get($key, collect());
            $completed = $monthShipments->whereIn('status', ['delivered', 'delayed'])->count();
            $delivered = $monthShipments->where('status', 'delivered')->count();

            $labels[] = $date->format('M y');
            $counts[] = $monthShipments->count();
            $onTime[] = $completed > 0 ? round($delivered / $completed * 100, 1) : 0.0;
        }

        return [$labels, $counts, $onTime];
    }

    /**
     * Percentage change between two values, null when no baseline.
     */
    private function percentChange(float $current, float $previous): ?float
    {
        if ($previous == 0.0) {
            return null;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }
}
