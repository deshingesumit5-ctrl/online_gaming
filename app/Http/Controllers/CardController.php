<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CardController extends Controller
{
    public const RANKS = ['Ace', 'King', 'Queen', 'Jack', 'Number', 'Joker', 'Custom'];

    public const SUITS = ['Spades', 'Hearts', 'Diamonds', 'Clubs', 'None'];

    public function __construct()
    {
        self::ensureSchema();
    }

    public static function ensureSchema(): void
    {
        if (Schema::hasTable('cards')) {
            return;
        }

        try {
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations/2026_09_23_000001_create_cards_table.php',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Card migrate in ensureSchema: ' . $e->getMessage());
        }

        if (Schema::hasTable('cards')) {
            return;
        }

        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('rank', 32);
            $table->string('suit', 32)->nullable();
            $table->integer('value')->nullable();
            $table->string('code')->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function index(Request $request): View|JsonResponse
    {
        $cards = Card::query()->latest('id')->get();
        $ranks = self::RANKS;
        $suits = self::SUITS;

        if ($this->wantsCardJson($request)) {
            return response()->json([
                'cards' => $cards,
            ]);
        }

        return view('admin.cards.index', compact('cards', 'ranks', 'suits'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $this->validatedPayload($request);
        unset($validated['photo']);
        $validated['photo_path'] = $this->storePhoto($request);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['code'] = $this->nullableCode($request);

        $card = Card::create($validated);

        if ($this->wantsCardJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Card saved.',
                'card' => $card->fresh(),
            ], 201);
        }

        return redirect()->route('admin.cards.index')->with('success', 'Card saved.');
    }

    public function update(Request $request, Card $card): JsonResponse|RedirectResponse
    {
        $validated = $this->validatedPayload($request, $card);
        unset($validated['photo']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['code'] = $this->nullableCode($request);

        if ($request->hasFile('photo')) {
            $this->deletePhoto($card->photo_path);
            $validated['photo_path'] = $this->storePhoto($request);
        }

        $card->update($validated);

        if ($this->wantsCardJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Card updated.',
                'card' => $card->fresh(),
            ]);
        }

        return redirect()->route('admin.cards.index')->with('success', 'Card updated.');
    }

    public function destroy(Request $request, Card $card): JsonResponse|RedirectResponse
    {
        $this->deletePhoto($card->photo_path);
        $card->delete();

        if ($this->wantsCardJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Card deleted.',
            ]);
        }

        return redirect()->route('admin.cards.index')->with('success', 'Card deleted.');
    }

    private function validatedPayload(Request $request, ?Card $card = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'rank' => ['required', Rule::in(self::RANKS)],
            'suit' => ['nullable', Rule::in(self::SUITS)],
            'value' => ['nullable', 'integer'],
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('cards', 'code')->ignore($card?->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function storePhoto(Request $request): ?string
    {
        if (!$request->hasFile('photo')) {
            return null;
        }

        return $request->file('photo')->store('cards', 'public');
    }

    private function deletePhoto(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function nullableCode(Request $request): ?string
    {
        $code = trim((string) $request->input('code', ''));

        return $code === '' ? null : $code;
    }

    private function wantsCardJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax() || $request->wantsJson();
    }
}
