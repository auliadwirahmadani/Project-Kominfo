@extends('layouts.admin.adminnav')

@section('title', 'Kelola Pengguna')
@section('page-title', 'Kelola Pengguna')

@section('content')

<div x-data="userManager()" class="bg-gradient-to-br from-red-100 via-red-200 to-red-300 min-h-screen p-8">

<div class="max-w-7xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">

        <div>
            <h1 class="text-2xl font-bold text-red-800">
                Kelola Pengguna
            </h1>

            <p class="text-sm text-gray-600">
                Total: <span class="font-semibold" x-text="filteredUsers.length"></span> pengguna
            </p>
        </div>

        <button
            type="button"
            @click="openTambah()"
            class="bg-red-700 hover:bg-red-800 text-white px-5 py-2 rounded-lg shadow">
            + Tambah User
        </button>

    </div>

    {{-- SEARCH & FILTER --}}
    <div class="flex flex-col md:flex-row gap-3">

        {{-- SEARCH --}}
        <input
            type="text"
            x-model="search"
            placeholder="Cari nama atau email..."
            class="w-full md:w-1/2 border px-4 py-2 rounded-lg shadow focus:ring focus:ring-red-300">

        {{-- FILTER ROLE --}}
        <select
            x-model="roleFilter"
            class="w-full md:w-1/4 border px-4 py-2 rounded-lg shadow">

            <option value="">Semua Role</option>
            <option value="admin">Admin</option>
            <option value="pengunjung">Pengunjung</option>
            <option value="produsen data">Produsen Data</option>
            <option value="verifikator">Verifikator</option>

        </select>

    </div>

    {{-- USER LIST --}}
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

        <template x-for="user in filteredUsers" :key="user.id">

            <div class="bg-white rounded-xl shadow-lg p-6">

                {{-- AVATAR --}}
                <div class="w-20 h-20 mx-auto rounded-full bg-red-200 flex items-center justify-center text-2xl font-bold text-red-700"
                     x-text="user.name.charAt(0).toUpperCase()">
                </div>

                {{-- INFO --}}
                <div class="text-center mt-3">

                    <h2 class="font-bold text-lg" x-text="user.name"></h2>

                    <p class="text-gray-500 text-sm" x-text="user.email"></p>

                    <span class="inline-block mt-2 px-3 py-1 text-xs rounded-full bg-red-100 text-red-700 capitalize"
                          x-text="user.role_name ?? 'pengunjung'">
                    </span>

                </div>

                {{-- ACTION --}}
                <div class="flex justify-center gap-2 mt-4">

                    {{-- EDIT --}}
                    <button
                        type="button"
                        @click="openEdit(user)"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                        Edit
                    </button>

                    {{-- DELETE --}}
                    <form method="POST"
                          :action="'/admin/pengguna/delete/' + user.id"
                          onsubmit="return confirm('Yakin hapus user?')">

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                            Hapus
                        </button>

                    </form>

                </div>

            </div>

        </template>

        {{-- EMPTY --}}
        <div x-show="filteredUsers.length === 0"
             class="col-span-full text-center bg-white p-10 rounded-xl shadow">

            Tidak ada user ditemukan

        </div>

    </div>

</div>


{{-- MODAL --}}
<div x-show="openModal"
     x-transition
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

<div @click.outside="closeModal()"
     class="bg-white w-full max-w-md p-6 rounded-xl shadow-xl">

<h2 class="text-xl font-bold mb-4 text-red-700"
    x-text="editMode ? 'Edit User' : 'Tambah User'">
</h2>


<form method="POST"
      :action="editMode 
        ? '{{ url('admin/pengguna/update') }}/' + form.id 
        : '{{ route('admin.storeUser') }}'">

    @csrf

    {{-- METHOD PUT --}}
    <template x-if="editMode">
        <input type="hidden" name="_method" value="PUT">
    </template>

    {{-- NAMA --}}
    <div class="mb-3">
        <label class="block font-semibold">Nama</label>
        <input type="text"
               name="name"
               x-model="form.name"
               required
               class="w-full border px-3 py-2 rounded">
    </div>

    {{-- EMAIL --}}
    <div class="mb-3">
        <label class="block font-semibold">Email</label>
        <input type="email"
               name="email"
               x-model="form.email"
               required
               class="w-full border px-3 py-2 rounded">
    </div>

    {{-- PASSWORD --}}
    <div class="mb-3">
        <label class="block font-semibold">Password</label>

        <input :type="showPassword ? 'text' : 'password'"
               name="password"
               :required="!editMode"
               class="w-full border px-3 py-2 rounded">
    </div>

    {{-- ROLE --}}
    <div class="mb-4">
        <label class="block font-semibold">Role</label>

        <select name="role_name"
                x-model="form.role_name"
                required
                class="w-full border px-3 py-2 rounded">

            <option value="admin">Admin</option>
            <option value="pengunjung">Pengunjung</option>
            <option value="produsen data">Produsen Data</option>
            <option value="verifikator">Verifikator</option>

        </select>
    </div>

    <div class="flex justify-end gap-2">

        <button type="button"
                @click="closeModal()"
                class="bg-gray-400 text-white px-4 py-2 rounded">
            Batal
        </button>

        <button type="submit"
                class="bg-red-600 text-white px-4 py-2 rounded">
            Simpan
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

function userManager(){

    return {

        openModal: false,
        editMode: false,
        showPassword: false,

        search: '',
        roleFilter: '',

        users: @json($users),

        form: {
            id: null,
            name: '',
            email: '',
            role_name: 'pengunjung'
        },


        get filteredUsers(){

            return this.users.filter(user => {

                let matchSearch =
                    user.name.toLowerCase().includes(this.search.toLowerCase())
                    ||
                    user.email.toLowerCase().includes(this.search.toLowerCase());

                let matchRole =
                    this.roleFilter === ''
                    ||
                    user.role_name === this.roleFilter;

                return matchSearch && matchRole;

            });

        },


        openTambah(){

            this.editMode = false;

            this.form = {
                id: null,
                name: '',
                email: '',
                role_name: 'pengunjung'
            };

            this.openModal = true;

        },


        openEdit(user){

            this.editMode = true;

            this.form = {
                id: user.id,
                name: user.name,
                email: user.email,
                role_name: user.role_name ?? 'pengunjung'
            };

            this.openModal = true;

        },


        closeModal(){

            this.openModal = false;

        }

    }

}

</script>

@endpush