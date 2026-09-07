<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePointRequestRequest;
use App\Models\PointRequest;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PointRequestController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
    }

    /**
     * Show point request submission form and user's history of requests.
     */
    public function index(): View
    {
        $user = Auth::user();
        $requests = PointRequest::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('points.request', compact('user', 'requests'));
    }

    /**
     * Submit a new point request to admin.
     */
    public function store(StorePointRequestRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        try {
            $pointRequest = $this->walletService->createPointRequest(
                user: $user,
                points: (int) $validated['points'],
                remarks: $validated['remarks'] ?? null
            );

            return redirect()->route('points.request')->with(
                'success_status',
                "Point request #{$pointRequest->request_id} for " . number_format($pointRequest->points_requested) . " PTS submitted! Admin will verify and approve your points shortly."
            );
        } catch (Exception $e) {
            return back()->withInput()->with('error_status', $e->getMessage());
        }
    }
}
