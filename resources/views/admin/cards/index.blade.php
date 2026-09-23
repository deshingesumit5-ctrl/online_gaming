@extends('layouts.admin')

@section('page-title', 'Card Master')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">
<style>
    .card-master-felt {
        background:
            radial-gradient(ellipse at top, rgba(212, 175, 55, 0.08), transparent 55%),
            linear-gradient(180deg, #0b1220 0%, #071018 100%);
    }
    .card-name-serif {
        font-family: Fraunces, Georgia, serif;
    }
    .card-master-table thead th {
        font-size: 11px;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #94a3b8;
        font-weight: 800;
        padding: 12px 14px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.18);
        white-space: nowrap;
        text-align: left;
    }
    .card-master-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.1);
        vertical-align: middle;
        color: #e2e8f0;
        font-size: 13px;
    }
    .card-master-table tbody tr:hover {
        background: rgba(245, 158, 11, 0.06);
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

    <div class="rounded-2xl border border-slate-700/70 bg-[#0c1324]/90 overflow-hidden" x-show="cards.length > 0">
        <div class="overflow-x-auto">
            <table class="card-master-table w-full min-w-[860px]">
                <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th class="w-24">Thumbnail</th>
                        <th>Card name</th>
                        <th>Rank</th>
                        <th>Suit</th>
                        <th>Value</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(card, index) in cards" :key="card.id">
                        <tr>
                            <td class="font-mono text-slate-400" x-text="index + 1"></td>
                            <td>
                                <div class="w-14 h-14 rounded-xl overflow-hidden border border-amber-500/30 bg-[#123c2c] flex items-center justify-center">
                                    <img x-show="card.photo_url"
                                         :src="card.photo_url"
                                         :alt="card.name"
                                         class="w-full h-full object-cover"
                                         @@error="onPhotoError(card)">
                                    <span x-show="!card.photo_url" class="text-2xl text-amber-200" x-text="suitIcon(card.suit)"></span>
                                </div>
                            </td>
                            <td>
                                <div class="font-semibold text-white" x-text="card.name"></div>
                                <div class="text-[11px] text-slate-400 mt-0.5" x-show="card.description" x-text="card.description"></div>
                            </td>
                            <td class="font-semibold text-amber-200" x-text="card.rank || '—'"></td>
                            <td>
                                <span class="inline-flex items-center gap-1.5">
                                    <span x-text="suitIcon(card.suit)"></span>
                                    <span x-text="card.suit || '—'"></span>
                                </span>
                            </td>
                            <td class="font-mono" x-text="card.value ?? '—'"></td>
                            <td class="font-mono text-slate-300" x-text="card.code || '—'"></td>
                            <td>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                                      :class="card.is_active ? 'bg-emerald-950 text-emerald-300 border border-emerald-500/40' : 'bg-slate-800 text-slate-400 border border-slate-700'"
                                      x-text="card.is_active ? 'Active' : 'Inactive'"></span>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="openEdit(card)" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-300 text-xs font-bold border border-slate-600">Edit</button>
                                    <button type="button" @click="removeCard(card)" class="px-3 py-1.5 rounded-lg bg-red-800 hover:bg-red-700 text-white text-xs font-bold">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
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
            onPhotoError(card) {
                if (!card || !card.photo_url) return;
                const pathId = '/admin/cards/' + card.id + '/photo';
                if (card.photo_url.indexOf(pathId) === -1) {
                    card.photo_url = pathId + '?t=' + Date.now();
                    return;
                }
                card.photo_url = '';
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
