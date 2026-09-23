<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CardController extends Controller
{
    public const RANKS = ['Ace', 'King', 'Queen', 'Jack', 'Number', 'Joker', 'Custom'];

    public const SUITS = ['Spades', 'Hearts', 'Diamonds', 'Clubs', 'None'];

    public function index(Request $request): View|JsonResponse
    {
        $cards = Card::query()->latest()->orderByDesc('id')->get();

        if ($this->wantsCardJson($request)) {
            return response()->json([
                'cards' => $cards,
            ]);
        }

        return view('admin.cards.index', compact('cards'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $this->validatedPayload($request);
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
