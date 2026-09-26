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
        self::ensurePublicPhotoStorage();

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

    public static function ensurePublicPhotoStorage(): void
    {
        $uploads = public_path('uploads/cards');
        if (!is_dir($uploads)) {
            @mkdir($uploads, 0755, true);
        }

        $link = public_path('storage');
        $target = storage_path('app/public');
        if (!file_exists($link) && is_dir($target)) {
            try {
                Artisan::call('storage:link');
            } catch (\Throwable $e) {
                if (is_dir($target) && !file_exists($link)) {
                    @symlink($target, $link);
                }
            }
        }
    }

    public function photo(Card $card)
    {
        $relative = str_replace('\\', '/', ltrim((string) $card->photo_path, '/'));
        if ($relative === '') {
            abort(404);
        }

        $basename = basename($relative);
        $candidates = array_values(array_unique(array_filter([
            public_path($relative),
            public_path('uploads/cards/'.$basename),
            public_path('storage/'.$relative),
            public_path('storage/cards/'.$basename),
            storage_path('app/public/'.$relative),
            storage_path('app/public/cards/'.$basename),
        ])));

        foreach ($candidates as $file) {
            if (is_file($file)) {
                $publicCopy = public_path('uploads/cards/'.$basename);
                if (!is_file($publicCopy) && is_dir(public_path('uploads/cards'))) {
                    @copy($file, $publicCopy);
                }

                return response()->file($file, [
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }

        if (Storage::disk('public')->exists($relative)) {
            return Storage::disk('public')->response($relative);
        }

        abort(404);
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
        unset($validated['photo'], $validated['shortcut_key']);
        $validated['photo_path'] = $this->storePhoto($request);
        $validated['rank'] = 'Custom';
        $validated['is_active'] = true;
        $validated['code'] = $this->shortcutKey($request);

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
        unset($validated['photo'], $validated['shortcut_key']);
        $validated['rank'] = $card->rank ?: 'Custom';
        $validated['is_active'] = true;
        $validated['code'] = $this->shortcutKey($request);

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
        $request->merge([
            'shortcut_key' => strtoupper(trim((string) $request->input('shortcut_key', ''))),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'shortcut_key' => [
                'required',
                'string',
                'max:8',
                'regex:/^[A-Za-z][A-Za-z0-9]{1,7}$/',
                Rule::unique('cards', 'code')->ignore($card?->id),
            ],
            'photo' => [$card ? 'nullable' : 'required', 'image', 'max:4096'],
        ]);
    }

    private function storePhoto(Request $request): ?string
    {
        if (!$request->hasFile('photo')) {
            return null;
        }

        self::ensurePublicPhotoStorage();

        $file = $request->file('photo');
        $name = $file->hashName();
        $dir = public_path('uploads/cards');
        $file->move($dir, $name);

        return 'uploads/cards/'.$name;
    }

    private function deletePhoto(?string $path): void
    {
        if (!$path) {
            return;
        }

        $relative = str_replace('\\', '/', ltrim($path, '/'));
        $publicFile = public_path($relative);
        if (is_file($publicFile)) {
            @unlink($publicFile);
        }

        if (Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }

        $storageFile = storage_path('app/public/'.$relative);
        if (is_file($storageFile)) {
            @unlink($storageFile);
        }
    }

    private function shortcutKey(Request $request): string
    {
        return strtoupper(trim((string) $request->input('shortcut_key', '')));
    }

    private function wantsCardJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax() || $request->wantsJson();
    }
}
