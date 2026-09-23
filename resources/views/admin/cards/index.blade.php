@extends('layouts.admin')

@section('page-title', 'Card Master')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">
<style>
    .card-master-felt {
        background:
            radial-gradient(ellipse at top, rgba(212, 175, 55, 0.12), transparent 55%),
            radial-gradient(circle at 20% 80%, rgba(16, 185, 129, 0.08), transparent 40%),
            linear-gradient(180deg, #0b3d2c 0%, #07261c 55%, #051910 100%);
    }
    .card-tile-face {
        background: linear-gradient(180deg, #f7f1de 0%, #efe4c4 100%);
        color: #1a1208;
        border: 1px solid rgba(212, 175, 55, 0.55);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.35);
    }
    .card-name-serif {
        font-family: Fraunces, Georgia, serif;
    }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="card-master-felt rounded-3xl border border-amber-500/20 p-5 sm:p-6 shadow-2xl"
     x-data="cardMaster()"
     x-cloak>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="card-name-serif text-2xl text-amber-300">Card Master</h2>
            <p class="text-sm text-emerald-100/70 mt-1">Manage the deck used by the admin panel.</p>
        </div>
        <button type="button"
                @click="openCreate()"
                class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-sm font-black shadow-lg shadow-amber-500/20">
            + New Card
        </button>
    </div>

    <div x-show="flash" x-transition class="mb-4 px-4 py-3 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 text-sm font-semibold" x-text="flash"></div>

    <template x-if="cards.length === 0">
        <div class="rounded-2xl border border-dashed border-amber-500/30 bg-black/20 py-16 px-6 text-center">
            <p class="card-name-serif text-xl text-amber-200">No cards yet. Add your first card to start the deck.</p>
        </div>
    </template>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <template x-for="card in cards" :key="card.id">
            <article class="card-tile-face rounded-2xl overflow-hidden">
                <div class="relative h-40 bg-[#123c2c]">
                    <img x-show="card.photo_url" :src="card.photo_url" :alt="card.name" class="w-full h-full object-cover">
                    <div x-show="!card.photo_url" class="w-full h-full flex items-center justify-center text-4xl text-amber-200" x-text="suitIcon(card.suit)"></div>
                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-slate-950/80 text-amber-300 border border-amber-500/40" x-text="card.rank"></span>
                    <span class="absolute top-2 right-2 text-lg" x-text="suitIcon(card.suit)"></span>
                    <span class="absolute bottom-2 right-2 w-2.5 h-2.5 rounded-full border border-white/50"
                          :class="card.is_active ? 'bg-emerald-400' : 'bg-slate-500'"
                          :title="card.is_active ? 'Active' : 'Inactive'"></span>
                </div>
                <div class="p-3.5">
                    <h3 class="card-name-serif text-lg leading-tight" x-text="card.name"></h3>
                    <p class="text-xs text-slate-700 mt-1">
                        Value: <span class="font-bold" x-text="card.value ?? '—'"></span>
                        <span class="mx-1 text-slate-400">·</span>
                        <span class="font-mono" x-text="card.code || 'no code'"></span>
                    </p>
                    <div class="flex gap-2 mt-3">
                        <button type="button" @click="openEdit(card)" class="flex-1 py-1.5 rounded-lg bg-slate-900 text-amber-300 text-xs font-bold">Edit</button>
                        <button type="button" @click="removeCard(card)" class="flex-1 py-1.5 rounded-lg bg-red-800 text-white text-xs font-bold">Delete</button>
                    </div>
                </div>
            </article>
        </template>
    </div>

    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center p-4" style="background: rgba(0,0,0,0.72);">
        <div class="w-full max-w-lg max-h-[92vh] overflow-y-auto rounded-3xl border border-amber-500/30 bg-[#071a14] p-5 sm:p-6 shadow-2xl" @click.outside="modalOpen = false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="card-name-serif text-xl text-amber-300" x-text="editingId ? 'Edit Card' : 'New Card'"></h3>
                <button type="button" @click="modalOpen = false" class="w-8 h-8 rounded-full bg-white/10 text-white">✕</button>
            </div>

            <form @submit.prevent="saveCard()" class="space-y-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-200/80 mb-1">Card name</label>
                    <input type="text" x-model="form.name" maxlength="100" class="w-full rounded-xl bg-slate-950 border border-slate-700 px-3 py-2 text-sm text-white focus:border-amber-400">
                    <p class="text-red-400 text-xs mt-1" x-show="errors.name" x-text="errors.name"></p>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-200/80 mb-1">Rank</label>
                    <select x-model="form.rank" class="w-full rounded-xl bg-slate-950 border border-slate-700 px-3 py-2 text-sm text-white focus:border-amber-400">
                        <option value="">Select rank</option>
                        @foreach($ranks as $rank)
                            <option value="{{ $rank }}">{{ $rank }}</option>
                        @endforeach
                    </select>
                    <p class="text-red-400 text-xs mt-1" x-show="errors.rank" x-text="errors.rank"></p>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-200/80 mb-1">Suit</label>
                    <select x-model="form.suit" class="w-full rounded-xl bg-slate-950 border border-slate-700 px-3 py-2 text-sm text-white focus:border-amber-400">
                        <option value="">Select suit</option>
                        @foreach($suits as $suit)
                            <option value="{{ $suit }}">{{ $suit }}</option>
                        @endforeach
                    </select>
                    <p class="text-red-400 text-xs mt-1" x-show="errors.suit" x-text="errors.suit"></p>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-200/80 mb-1">Value</label>
                    <input type="number" x-model="form.value" class="w-full rounded-xl bg-slate-950 border border-slate-700 px-3 py-2 text-sm text-white focus:border-amber-400">
                    <p class="text-red-400 text-xs mt-1" x-show="errors.value" x-text="errors.value"></p>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-200/80 mb-1">Card code</label>
                    <input type="text" x-model="form.code" maxlength="20" placeholder="AS-01" class="w-full rounded-xl bg-slate-950 border border-slate-700 px-3 py-2 text-sm text-white focus:border-amber-400">
                    <p class="text-red-400 text-xs mt-1" x-show="errors.code" x-text="errors.code"></p>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-200/80 mb-1">Photo</label>
                    <input type="file" accept="image/*" @change="onPhotoChange($event)" class="w-full text-xs text-slate-300">
                    <img x-show="previewUrl" :src="previewUrl" alt="Preview" class="mt-2 h-28 rounded-xl object-cover border border-amber-500/30">
                    <p class="text-red-400 text-xs mt-1" x-show="errors.photo" x-text="errors.photo"></p>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-200/80 mb-1">Description</label>
                    <textarea x-model="form.description" rows="3" maxlength="1000" class="w-full rounded-xl bg-slate-950 border border-slate-700 px-3 py-2 text-sm text-white focus:border-amber-400"></textarea>
                    <p class="text-red-400 text-xs mt-1" x-show="errors.description" x-text="errors.description"></p>
                </div>
                <label class="flex items-center gap-2 text-sm text-emerald-100">
                    <input type="checkbox" x-model="form.is_active" class="rounded border-slate-600">
                    Active
                </label>
                <p class="text-red-400 text-xs" x-show="errors.is_active" x-text="errors.is_active"></p>

                <button type="submit"
                        :disabled="saving"
                        class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:opacity-60 disabled:cursor-not-allowed text-slate-950 font-black">
                    <span x-text="saving ? 'Saving…' : 'Save'"></span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
<script>
    function cardMaster() {
        return {
            cards: @json($cards),
            modalOpen: false,
            saving: false,
            editingId: null,
            flash: '',
            previewUrl: '',
            photoFile: null,
            errors: {},
            form: {
                name: '',
                rank: '',
                suit: '',
                value: '',
                code: '',
                description: '',
                is_active: true
            },
            csrf() {
                return document.querySelector('meta[name="csrf-token"]')?.content || '';
            },
            suitIcon(suit) {
                return { Spades: '♠', Hearts: '♥', Diamonds: '♦', Clubs: '♣', None: '★' }[suit] || '🂠';
            },
            resetForm() {
                this.form = { name: '', rank: '', suit: '', value: '', code: '', description: '', is_active: true };
                this.errors = {};
                this.photoFile = null;
                this.previewUrl = '';
                this.editingId = null;
            },
            openCreate() {
                this.resetForm();
                this.modalOpen = true;
            },
            openEdit(card) {
                this.resetForm();
                this.editingId = card.id;
                this.form.name = card.name || '';
                this.form.rank = card.rank || '';
                this.form.suit = card.suit || '';
                this.form.value = card.value ?? '';
                this.form.code = card.code || '';
                this.form.description = card.description || '';
                this.form.is_active = !!card.is_active;
                this.previewUrl = card.photo_url || '';
                this.modalOpen = true;
            },
            onPhotoChange(event) {
                const file = event.target.files && event.target.files[0];
                this.photoFile = file || null;
                if (this.previewUrl && this.previewUrl.startsWith('blob:')) {
                    URL.revokeObjectURL(this.previewUrl);
                }
                this.previewUrl = file ? URL.createObjectURL(file) : '';
            },
            fieldError(errors, key) {
                const val = errors ? errors[key] : null;
                return Array.isArray(val) ? val[0] : (val || '');
            },
            async refreshCards() {
                const res = await fetch(@json(route('admin.cards.index')), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                this.cards = data.cards || [];
            },
            async saveCard() {
                this.saving = true;
                this.errors = {};
                const fd = new FormData();
                fd.append('name', this.form.name);
                fd.append('rank', this.form.rank);
                fd.append('suit', this.form.suit || '');
                if (this.form.value !== '' && this.form.value !== null) fd.append('value', this.form.value);
                fd.append('code', this.form.code || '');
                fd.append('description', this.form.description || '');
                fd.append('is_active', this.form.is_active ? '1' : '0');
                if (this.photoFile) fd.append('photo', this.photoFile);

                let url = @json(route('admin.cards.store'));
                if (this.editingId) {
                    url = @json(url('admin/cards')) + '/' + this.editingId;
                    fd.append('_method', 'PUT');
                }

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrf()
                        },
                        body: fd
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.status === 422) {
                        const bag = data.errors || {};
                        this.errors = {
                            name: this.fieldError(bag, 'name'),
                            rank: this.fieldError(bag, 'rank'),
                            suit: this.fieldError(bag, 'suit'),
                            value: this.fieldError(bag, 'value'),
                            code: this.fieldError(bag, 'code'),
                            description: this.fieldError(bag, 'description'),
                            photo: this.fieldError(bag, 'photo'),
                            is_active: this.fieldError(bag, 'is_active')
                        };
                        return;
                    }
                    if (!res.ok) return;
                    this.flash = data.message || (this.editingId ? 'Card updated.' : 'Card saved.');
                    this.modalOpen = false;
                    await this.refreshCards();
                    setTimeout(() => { this.flash = ''; }, 2500);
                } finally {
                    this.saving = false;
                }
            },
            async removeCard(card) {
                if (!confirm('Delete this card?')) return;
                const res = await fetch(@json(url('admin/cards')) + '/' + card.id, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrf()
                    }
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) return;
                this.flash = data.message || 'Card deleted.';
                await this.refreshCards();
                setTimeout(() => { this.flash = ''; }, 2500);
            }
        };
    }
</script>
@endpush
