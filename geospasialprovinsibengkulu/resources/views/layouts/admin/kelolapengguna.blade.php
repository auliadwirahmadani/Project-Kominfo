@extends('layouts.admin.adminnav')
@section('title', 'Kelola Pengguna - Admin')
@section('page-title', 'Kelola Pengguna')

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .user-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #f3f4f6;
        box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        transition: all 0.25s ease;
        overflow: hidden;
        position: relative;
    }
    .user-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #991b1b, #dc2626);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }
    .user-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(153,27,27,0.12);
        border-color: #fecaca;
    }
    .user-card:hover::before { transform: scaleX(1); }

    .avatar-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #991b1b, #dc2626);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        color: white;
        margin: 0 auto 1rem;
        border: 3px solid white;
        box-shadow: 0 4px 14px rgba(153,27,27,0.3);
        flex-shrink: 0;
    }
    .avatar-circle img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .role-admin     { background: #fef3c7; color: #92400e; }
    .role-produsen  { background: #dbeafe; color: #1e40af; }
    .role-verifikator { background: #d1fae5; color: #065f46; }
    .role-default   { background: #f3f4f6; color: #374151; }

    .search-input {
        width: 100%;
        padding: 0.65rem 1rem 0.65rem 2.75rem;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: white;
    }
    .search-input:focus {
        border-color: #991b1b;
        box-shadow: 0 0 0 3px rgba(153,27,27,0.08);
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.55);
        backdrop-filter: blur(4px);
        z-index: 50;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .modal-box {
        background: white;
        border-radius: 24px;
        width: 100%;
        max-width: 480px;
        box-shadow: 0 24px 60px rgba(0,0,0,0.2);
        overflow: hidden;
        max-height: 90vh;
        overflow-y: auto;
    }
    .modal-header {
        padding: 1.5rem 1.75rem 1rem;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-body { padding: 1.5rem 1.75rem; }
    .modal-footer {
        padding: 1rem 1.75rem 1.5rem;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        border-top: 1px solid #f3f4f6;
    }

    .form-group { margin-bottom: 1rem; }
    .form-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.35rem;
    }
    .form-control {
        width: 100%;
        padding: 0.6rem 0.9rem;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.9rem;
        color: #1f2937;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: #fafafa;
    }
    .form-control:focus {
        border-color: #991b1b;
        box-shadow: 0 0 0 3px rgba(153,27,27,0.08);
        background: white;
    }

    .btn-primary-red {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.6rem 1.25rem;
        background: linear-gradient(135deg, #991b1b, #dc2626);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        border: none;
        transition: all 0.2s;
        box-shadow: 0 2px 8px rgba(153,27,27,0.3);
    }
    .btn-primary-red:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(153,27,27,0.4);
    }
    .btn-outline-gray {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.6rem 1.25rem;
        background: white;
        color: #374151;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-outline-gray:hover { background: #f9fafb; border-color: #d1d5db; }

    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: white;
        border: 1px solid #f3f4f6;
        border-radius: 50px;
        padding: 0.4rem 1rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #374151;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #9ca3af;
    }
    .empty-icon {
        font-size: 3.5rem;
        margin-bottom: 1rem;
        opacity: 0.4;
    }
</style>
@endpush

@section('content')
<div x-data="userManager()" x-cloak class="space-y-6">

    {{-- ===================== HEADER ===================== --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-800 flex items-center gap-2">
                <span class="w-9 h-9 bg-gradient-to-br from-red-700 to-red-500 rounded-xl flex items-center justify-center text-white text-sm">
                    <i class="fas fa-users"></i>
                </span>
                Kelola Pengguna
            </h1>
            <p class="text-sm text-gray-500 mt-1 ml-11">Manajemen akun dan hak akses pengguna platform</p>
        </div>

        <button
            type="button"
            @click="openTambah()"
            class="btn-primary-red"
        >
            <i class="fas fa-plus"></i> Tambah Pengguna
        </button>
    </div>

    {{-- ===================== STATS ===================== --}}
    <div class="flex flex-wrap gap-3">
        <span class="stat-pill">
            <span class="w-2 h-2 rounded-full bg-gray-400"></span>
            Total: <strong x-text="users.length"></strong> pengguna
        </span>
        <span class="stat-pill">
            <span class="w-2 h-2 rounded-full bg-red-500"></span>
            Ditampilkan: <strong x-text="filteredUsers.length"></strong>
        </span>
        @foreach($roles as $r)
        <span class="stat-pill text-xs">
            {{ ucwords($r->role_name) }}:
            <strong>{{ \App\Models\User::where('role_id', $r->role_id)->count() }}</strong>
        </span>
        @endforeach
    </div>

    {{-- ===================== SEARCH & FILTER ===================== --}}
    <div class="flex flex-col md:flex-row gap-3">

        {{-- Search --}}
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input
                type="text"
                x-model="search"
                placeholder="Cari nama atau email pengguna..."
                class="search-input"
                id="searchUserInput"
            >
        </div>

        {{-- Filter Role --}}
        <select
            x-model="roleFilter"
            class="form-control md:w-48"
            style="padding: 0.65rem 0.9rem;"
        >
            <option value="">Semua Role</option>
            @foreach($roles as $r)
                <option value="{{ $r->role_name }}">{{ ucwords($r->role_name) }}</option>
            @endforeach
        </select>

    </div>

    {{-- ===================== USER GRID ===================== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">

        <template x-for="user in filteredUsers" :key="user.user_id">
            <div class="user-card p-6 text-center">

                {{-- Avatar --}}
                <div class="avatar-circle">
                    <template x-if="user.photo_url">
                        <img :src="user.photo_url" :alt="user.name">
                    </template>
                    <template x-if="!user.photo_url">
                        <span x-text="user.name.charAt(0).toUpperCase()"></span>
                    </template>
                </div>

                {{-- Info --}}
                <h3 class="font-bold text-gray-800 text-base mb-0.5 line-clamp-1" x-text="user.name"></h3>
                <p class="text-gray-500 text-xs mb-2 truncate" x-text="user.email"></p>

                {{-- Role Badge --}}
                <span
                    class="role-badge mb-3 inline-flex"
                    :class="{
                        'role-admin':      user.role_name === 'Admin',
                        'role-produsen':   user.role_name === 'Produsen Data',
                        'role-verifikator':user.role_name === 'Verifikator',
                        'role-default':    !['Admin','Produsen Data','Verifikator'].includes(user.role_name)
                    }"
                    x-text="user.role_name ?? 'Pengunjung'"
                ></span>

                {{-- Instansi (jika ada) --}}
                <p x-show="user.instansi"
                   class="text-xs text-gray-400 mb-3 flex items-center justify-center gap-1 line-clamp-1">
                    <i class="fas fa-building text-gray-300"></i>
                    <span x-text="user.instansi"></span>
                </p>

                {{-- Divider --}}
                <div class="border-t border-gray-100 pt-4 mt-2 flex justify-center gap-2">

                    {{-- EDIT --}}
                    <button
                        type="button"
                        @click="openEdit(user)"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-semibold transition"
                    >
                        <i class="fas fa-pen text-[10px]"></i> Edit
                    </button>

                    {{-- DELETE via separate form --}}
                    <button
                        type="button"
                        @click="confirmDelete(user.user_id, user.name)"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-xs font-semibold transition"
                    >
                        <i class="fas fa-trash text-[10px]"></i> Hapus
                    </button>

                </div>

            </div>
        </template>

        {{-- Empty State --}}
        <div
            x-show="filteredUsers.length === 0"
            class="col-span-full"
        >
            <div class="empty-state bg-white rounded-2xl border border-gray-100 shadow-sm">
                <div class="empty-icon">👤</div>
                <p class="font-semibold text-base text-gray-600">Tidak ada pengguna ditemukan</p>
                <p class="text-sm text-gray-400 mt-1">Coba ubah kata kunci pencarian atau filter role</p>
            </div>
        </div>

    </div>

    {{-- ===================== HIDDEN DELETE FORM ===================== --}}
    <form id="deleteForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    {{-- ===================== MODAL TAMBAH/EDIT ===================== --}}
    <div
        x-show="openModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="modal-backdrop"
        @click.self="closeModal()"
    >
        <div
            class="modal-box"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
        >
            {{-- Header --}}
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-700 to-red-500 flex items-center justify-center text-white">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800" x-text="editMode ? 'Edit Pengguna' : 'Tambah Pengguna'"></h2>
                </div>
                <button @click="closeModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 transition">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            {{-- Form --}}
            <form
                method="POST"
                :action="editMode
                    ? '/admin/pengguna/update/' + form.user_id
                    : '{{ route('admin.storeUser') }}'"
            >
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="modal-body space-y-1">

                    {{-- Nama --}}
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            name="name"
                            x-model="form.name"
                            required
                            class="form-control"
                            placeholder="Masukkan nama lengkap"
                        >
                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        <label class="form-label">Email <span class="text-red-500">*</span></label>
                        <input
                            type="email"
                            name="email"
                            x-model="form.email"
                            required
                            class="form-control"
                            placeholder="contoh@email.com"
                        >
                    </div>

                    {{-- Password --}}
                    <div class="form-group">
                        <label class="form-label">
                            Password
                            <span x-show="!editMode" class="text-red-500">*</span>
                            <span x-show="editMode" class="text-gray-400 font-normal text-xs">(kosongkan jika tidak diubah)</span>
                        </label>
                        <div class="relative">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                :required="!editMode"
                                class="form-control pr-10"
                                placeholder="Minimal 6 karakter"
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Role --}}
                    <div class="form-group">
                        <label class="form-label">Role / Hak Akses <span class="text-red-500">*</span></label>
                        <select
                            name="role_name"
                            x-model="form.role_name"
                            required
                            class="form-control"
                        >
                            @foreach($roles as $r)
                                <option value="{{ $r->role_name }}">{{ ucwords($r->role_name) }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" @click="closeModal()" class="btn-outline-gray">
                        <i class="fas fa-times text-xs"></i> Batal
                    </button>
                    <button type="submit" class="btn-primary-red">
                        <i class="fas fa-save text-xs"></i>
                        <span x-text="editMode ? 'Simpan Perubahan' : 'Tambah Pengguna'"></span>
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
function userManager() {
    return {
        openModal: false,
        editMode: false,
        showPassword: false,
        search: '',
        roleFilter: '',

        // Data pengguna sudah disiapkan bersih dari controller
        users: @json($usersJson),

        form: {
            user_id: null,
            name: '',
            email: '',
            role_name: '',
        },

        get filteredUsers() {
            return this.users.filter(user => {
                const q = this.search.toLowerCase();
                const matchSearch =
                    user.name.toLowerCase().includes(q) ||
                    user.email.toLowerCase().includes(q) ||
                    (user.instansi && user.instansi.toLowerCase().includes(q));

                const matchRole =
                    this.roleFilter === '' ||
                    user.role_name === this.roleFilter;

                return matchSearch && matchRole;
            });
        },

        openTambah() {
            this.editMode = false;
            this.showPassword = false;
            this.form = { user_id: null, name: '', email: '', role_name: '' };
            this.openModal = true;
        },

        openEdit(user) {
            this.editMode = true;
            this.showPassword = false;
            this.form = {
                user_id:   user.user_id,
                name:      user.name,
                email:     user.email,
                role_name: user.role_name ?? '',
            };
            this.openModal = true;
        },

        confirmDelete(userId, userName) {
            if (!confirm(`Yakin ingin menghapus pengguna "${userName}"? Tindakan ini tidak bisa dibatalkan.`)) return;
            const form = document.getElementById('deleteForm');
            form.action = '/admin/pengguna/delete/' + userId;
            form.submit();
        },

        closeModal() {
            this.openModal = false;
        },
    }
}
</script>
@endpush