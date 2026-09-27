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
            <table class="card-master-table w-full min-w-[640px]">
                <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th class="w-24">Thumbnail</th>
                        <th>Card name</th>
                        <th>Short Cut Key</th>
                        <th class="text-center">Actions</th>
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
                                    <span x-show="!card.photo_url" class="text-2xl text-amber-200">🂠</span>
                                </div>
                            </td>
                            <td>
                                <div class="font-semibold text-white" x-text="card.name"></div>
                            </td>
                            <td class="font-mono font-bold text-amber-200" x-text="card.code || '—'"></td>
                            <td>
                                <div class="flex flex-row items-center justify-center gap-2">
                                    <button type="button" @click="openEdit(card)" title="Edit" aria-label="Edit card" class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-300 border border-slate-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M4 20h4.5L19.768 8.768a2.5 2.5 0 00-3.536-3.536L5 16.5V20z"/></svg>
                                    </button>
                                    <button type="button" @click="askDelete(card)" title="Delete" aria-label="Delete card" class="w-9 h-9 rounded-lg bg-red-800 hover:bg-red-700 text-white flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center p-4" style="background: rgba(0,0,0,0.72);">
        <div class="flex flex-col items-center justify-center text-center rounded-3xl border border-red-500/40 shadow-2xl px-6"
             style="width: min(88vw, 360px); aspect-ratio: 1 / 1; background: radial-gradient(circle at 50% 20%, #3f1212 0%, #140808 70%);"
             @click.outside="deleteOpen = false">
            <div class="w-14 h-14 rounded-2xl bg-red-500/15 border border-red-400/50 flex items-center justify-center text-red-300 mb-4">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
            </div>
            <p class="card-name-serif text-xl text-white leading-snug px-2">Do you want to delete this card?</p>
            <p class="text-xs text-red-200/80 mt-2" x-text="pendingDelete ? pendingDelete.name : ''"></p>
            <div class="flex gap-3 w-full mt-6">
                <button type="button" @click="deleteOpen = false" class="flex-1 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-bold">Cancel</button>
                <button type="button" @click="confirmDelete()" class="flex-1 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white text-sm font-bold">Delete</button>
            </div>
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
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-200/80 mb-1">Short Cut Key</label>
                    <input type="text" x-model="form.shortcut_key" maxlength="8" placeholder="A1" class="w-full rounded-xl bg-slate-950 border border-slate-700 px-3 py-2 text-sm text-white focus:border-amber-400 uppercase">
                    <p class="text-[11px] text-slate-400 mt-1">Example: A1, A2. Press this on the live camera to show this card photo.</p>
                    <p class="text-red-400 text-xs mt-1" x-show="errors.shortcut_key" x-text="errors.shortcut_key"></p>
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-emerald-200/80 mb-1">Photo</label>
                    <input type="file" accept="image/*" @change="onPhotoChange($event)" class="w-full text-xs text-slate-300">
                    <img x-show="previewUrl" :src="previewUrl" alt="Preview" class="mt-2 h-28 rounded-xl object-cover border border-amber-500/30">
                    <p class="text-red-400 text-xs mt-1" x-show="errors.photo" x-text="errors.photo"></p>
                </div>

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
            deleteOpen: false,
            pendingDelete: null,
            saving: false,
            editingId: null,
            flash: '',
            previewUrl: '',
            photoFile: null,
            errors: {},
            form: {
                name: '',
                shortcut_key: ''
            },
            csrf() {
                return document.querySelector('meta[name="csrf-token"]')?.content || '';
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
                this.form = { name: '', shortcut_key: '' };
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
                this.form.shortcut_key = card.code || '';
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
                fd.append('shortcut_key', (this.form.shortcut_key || '').trim());
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
                            shortcut_key: this.fieldError(bag, 'shortcut_key'),
                            photo: this.fieldError(bag, 'photo')
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
            askDelete(card) {
                this.pendingDelete = card;
                this.deleteOpen = true;
            },
            async confirmDelete() {
                const card = this.pendingDelete;
                this.deleteOpen = false;
                if (!card) return;
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
                this.pendingDelete = null;
                await this.refreshCards();
                setTimeout(() => { this.flash = ''; }, 2500);
            }
        };
    }
</script>
@endpush
