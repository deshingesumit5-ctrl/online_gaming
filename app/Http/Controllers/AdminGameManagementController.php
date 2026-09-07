<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameRound;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminGameManagementController extends Controller
{
    /**
     * Display Game & Room Management dashboard.
     */
    public function index(): View
    {
        $games = Game::with(['rooms.currentRound', 'creator'])
            ->latest('id')
            ->get();

        $stats = [
            'total_games' => $games->count(),
            'active_games' => $games->where('status', 'open')->count(),
            'total_rooms' => $games->sum(fn($g) => $g->rooms->count()),
            'live_rooms' => $games->sum(fn($g) => $g->rooms->where('status', 'live')->count()),
        ];

        return view('admin.games.index', compact('games', 'stats'));
    }

    /**
     * Store a newly created Game.
     */
    public function storeGame(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', 'in:open,closed,scheduled'],
            'start_time' => ['nullable', 'date'],
        ]);

        $game = Game::create([
            'name' => $validated['name'],
            'status' => $validated['status'],
            'start_time' => $validated['start_time'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.games.index')->with('success', "Game '{$game->name}' created successfully.");
    }

    /**
     * Update an existing Game.
     */
    public function updateGame(Request $request, int $id): RedirectResponse
    {
        $game = Game::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', 'in:open,closed,scheduled'],
            'start_time' => ['nullable', 'date'],
        ]);

        $game->update([
            'name' => $validated['name'],
            'status' => $validated['status'],
            'start_time' => $validated['start_time'] ?? null,
        ]);

        return redirect()->route('admin.games.index')->with('success', "Game '{$game->name}' updated successfully.");
    }

    /**
     * Quickly toggle Game Active / Inactive.
     */
    public function toggleGameStatus(int $id): RedirectResponse
    {
        $game = Game::findOrFail($id);
        $newStatus = $game->status === 'open' ? 'closed' : 'open';
        $game->update(['status' => $newStatus]);

        $statusLabel = $newStatus === 'open' ? 'ACTIVATED' : 'DEACTIVATED';
        return redirect()->route('admin.games.index')->with('success', "Game '{$game->name}' has been {$statusLabel}.");
    }

    /**
     * Store a newly created Room for a Game.
     */
    public function storeRoom(Request $request, int $gameId): RedirectResponse
    {
        $game = Game::findOrFail($gameId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'betting_duration' => ['required', 'integer', 'min:5', 'max:300'],
            'cancellation_duration' => ['required', 'integer', 'min:0', 'max:300'],
            'allowed_denominations' => ['nullable', 'string'],
            'live_stream_url' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:live,closed'],
            'start_time' => ['nullable', 'date'],
        ]);

        // Parse denominations into integer array
        $denominations = $this->parseDenominations($validated['allowed_denominations'] ?? null);

        $room = Room::create([
            'game_id' => $game->id,
            'name' => $validated['name'],
            'betting_duration' => $validated['betting_duration'],
            'cancellation_duration' => $validated['cancellation_duration'],
            'allowed_denominations' => $denominations,
            'live_stream_url' => $validated['live_stream_url'] ?? null,
            'status' => $validated['status'],
            'start_time' => $validated['start_time'] ?? null,
        ]);

        // Automatically initialize Round #1 for live play
        GameRound::create([
            'room_id' => $room->id,
            'round_number' => 1,
            'status' => 'open',
            'first_card' => null,
        ]);

        return redirect()->route('admin.games.index')->with('success', "Table/Room '{$room->name}' created and initialized under {$game->name}.");
    }

    /**
     * Update an existing Room.
     */
    public function updateRoom(Request $request, int $id): RedirectResponse
    {
        $room = Room::with('game')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'betting_duration' => ['required', 'integer', 'min:5', 'max:300'],
            'cancellation_duration' => ['required', 'integer', 'min:0', 'max:300'],
            'allowed_denominations' => ['nullable', 'string'],
            'live_stream_url' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:live,closed'],
            'start_time' => ['nullable', 'date'],
        ]);

        $denominations = $this->parseDenominations($validated['allowed_denominations'] ?? null);

        $room->update([
            'name' => $validated['name'],
            'betting_duration' => $validated['betting_duration'],
            'cancellation_duration' => $validated['cancellation_duration'],
            'allowed_denominations' => $denominations,
            'live_stream_url' => $validated['live_stream_url'] ?? null,
            'status' => $validated['status'],
            'start_time' => $validated['start_time'] ?? null,
        ]);

        return redirect()->route('admin.games.index')->with('success', "Table/Room '{$room->name}' updated successfully.");
    }

    /**
     * Quickly toggle Room Live / Closed.
     */
    public function toggleRoomStatus(int $id): RedirectResponse
    {
        $room = Room::findOrFail($id);
        $newStatus = $room->status === 'live' ? 'closed' : 'live';
        $room->update(['status' => $newStatus]);

        $statusLabel = $newStatus === 'live' ? 'ACTIVATED (LIVE)' : 'CLOSED';
        return redirect()->route('admin.games.index')->with('success', "Table/Room '{$room->name}' has been {$statusLabel}.");
    }

    /**
     * Helper to parse comma-separated denominations to integer array.
     */
    private function parseDenominations(?string $input): array
    {
        if (empty($input)) {
            return [100, 500, 1000, 2000, 5000];
        }

        $parts = explode(',', $input);
        $result = [];
        foreach ($parts as $part) {
            $num = (int) trim($part);
            if ($num > 0) {
                $result[] = $num;
            }
        }

        return !empty($result) ? array_values(array_unique($result)) : [100, 500, 1000, 2000, 5000];
    }
}
