@extends('layouts.produsen.produsennav')
@section('title', 'Profil Saya - Produsen Data')
@section('page-title', 'Profil Saya')

@push('styles')
<style>
    .profile-upload-wrap {
        position: relative;
        width: 128px;
        height: 128px;
        margin: 0 auto 1.5rem;
    }
    .profile-upload-wrap img,
    .profile-upload-wrap .avatar-placeholder {
        width: 128px;
        height: 128px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 4px 20px rgba(153,27,27,0.25);
    }
    .profile-upload-wrap .avatar-placeholder {
        background: linear-gradient(135deg, #991b1b, #dc2626);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 700;
        color: white;
    }
    .upload-btn-overlay {
        position: absolute;
        bottom: 4px;
        right: 4px;
        width: 36px;
        height: 36px;
        background: #991b1b;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        border: 2px solid white;
    }
    .upload-btn-overlay:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(153,27,27,0.4);
    }
    .form-section {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border: 1px solid #f3f4f6;
    }
    .form-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.4rem;
    }
    .form-input {
        width: 100%;
        padding: 0.65rem 1rem;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.95rem;
        color: #1f2937;
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
        background: #fafafa;
    }
    .form-input:focus {
        border-color: #991b1b;
        box-shadow: 0 0 0 3px rgba(153,27,27,0.1);
        background: white;
    }
    .form-input.error { border-color: #ef4444; }
    textarea.form-input { resize: vertical; min-height: 100px; }
    .char-counter {
        font-size: 0.75rem;
        color: #9ca3af;
        text-align: right;
        margin-top: 0.25rem;
    }
    .photo-preview-hint {
        font-size: 0.75rem;
        color: #6b7280;
        text-align: center;
        margin-top: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="bg-gradient-to-br from-red-50 via-white to-red-50 min-h-screen p-4 md:p-8">
    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Header banner --}}
        <div class="bg-gradient-to-r from-red-800 to-[#8b0000] rounded-2xl p-6 shadow-xl text-white flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-id-card text-2xl text-yellow-300"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black">Profil Saya</h1>
                <p class="text-red-100 text-sm mt-0.5">Kelola foto profil, nama, dan informasi instansi Anda</p>
            </div>
        </div>

        {{-- Pending Data Banner --}}
        @if(isset($profile) && $profile->pending_data)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-xl shadow-sm relative overflow-hidden">
            <div class="flex items-start gap-4">
                <i class="fas fa-clock text-yellow-500 text-xl mt-0.5"></i>
                <div>
                    <h3 class="font-bold text-yellow-800">Menunggu Verifikasi</h3>
                    <p class="text-sm text-yellow-700 mt-1">Anda memiliki perubahan profil yang sedang menunggu persetujuan dari Verifikator. Perubahan Anda (termasuk foto) tidak akan tampil di publik sebelum disetujui. Jika Anda menyimpannya lagi, data tunggu sebelumnya akan tertimpa dengan yang baru.</p>
                </div>
            </div>
        </div>
        @endif

        {{-- === ALERT VALIDASI === --}}
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 shrink-0"></i>
                <div>
                    <p class="font-bold text-sm mb-1">Terjadi kesalahan:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl flex items-center gap-3">
                <i class="fas fa-check-circle text-green-500 shrink-0"></i>
                <span class="font-semibold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        <form
            action="{{ route('produsen.profile.update') }}"
            method="POST"
            enctype="multipart/form-data"
            id="profileForm"
        >
            @csrf

            {{-- === FOTO PROFIL === --}}
            <div class="form-section text-center">
                <h2 class="text-base font-bold text-gray-700 mb-4 flex items-center justify-center gap-2">
                    <i class="fas fa-camera text-red-600"></i> Foto Profil
                </h2>

                <div class="profile-upload-wrap">
                    {{-- Preview foto --}}
                    @if($profile->photo && file_exists(public_path('storage/' . $profile->photo)))
                        <img
                            src="{{ asset('storage/' . $profile->photo) }}"
                            alt="Foto Profil"
                            id="photoPreview"
                        >
                    @else
                        <div class="avatar-placeholder" id="avatarPlaceholder">
                            {{ strtoupper(substr($user->name ?? 'P', 0, 1)) }}
                        </div>
                        <img src="" alt="" id="photoPreview" class="hidden" style="position:absolute;top:0;left:0;">
                    @endif

                    {{-- Tombol upload --}}
                    <label class="upload-btn-overlay" for="photoInput" title="Ganti foto">
                        <i class="fas fa-camera text-white text-xs"></i>
                    </label>
                    <input
                        type="file"
                        name="photo"
                        id="photoInput"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                    >
                </div>
                <p class="photo-preview-hint">JPG, PNG, atau WebP — maks. 2MB</p>

                @error('photo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- === INFORMASI DASAR === --}}
            <div class="form-section">
                <h2 class="text-base font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fas fa-user text-red-600"></i> Informasi Dasar
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nama Instansi / Akun --}}
                    <div class="md:col-span-2">
                        <label class="form-label" for="name">Nama Instansi / OPD <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            class="form-input @error('name') error @enderror"
                            placeholder="Contoh: Dinas Kominfo Provinsi Bengkulu"
                            required
                        >
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle"></i> Nama ini akan digunakan sebagai nama akun formil OPD Anda pada platform.</p>
                    </div>

                    {{-- No. HP --}}
                    <div>
                        <label class="form-label" for="no_hp">Nomor HP / Kontak</label>
                        <input
                            type="text"
                            id="no_hp"
                            name="no_hp"
                            value="{{ old('no_hp', $profile->no_hp) }}"
                            class="form-input @error('no_hp') error @enderror"
                            placeholder="08xxxxxxxxxx"
                        >
                        @error('no_hp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Alamat --}}
                    <div>
                        <label class="form-label" for="alamat">Alamat Kantor</label>
                        <input
                            type="text"
                            id="alamat"
                            name="alamat"
                            value="{{ old('alamat', $profile->alamat) }}"
                            class="form-input @error('alamat') error @enderror"
                            placeholder="Jalan / Kelurahan / Kota"
                        >
                        @error('alamat') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- === BIO === --}}
            <div class="form-section">
                <h2 class="text-base font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <i class="fas fa-align-left text-red-600"></i> Bio / Deskripsi Instansi
                </h2>

                <label class="form-label" for="bio">Bio</label>
                <textarea
                    id="bio"
                    name="bio"
                    class="form-input @error('bio') error @enderror"
                    placeholder="Tuliskan deskripsi singkat tentang instansi atau peran Anda sebagai produsen data..."
                    maxlength="1000"
                    oninput="document.getElementById('bioCount').textContent = this.value.length"
                >{{ old('bio', $profile->bio) }}</textarea>
                <div class="char-counter"><span id="bioCount">{{ strlen($profile->bio ?? '') }}</span>/1000 karakter</div>
                @error('bio') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- === TOMBOL SIMPAN === --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('produsen.dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition font-medium text-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button
                    type="submit"
                    id="saveBtn"
                    class="inline-flex items-center gap-2 px-7 py-2.5 bg-gradient-to-r from-red-800 to-red-600 text-white rounded-xl hover:from-red-700 hover:to-red-500 transition font-bold text-sm shadow-lg shadow-red-900/20 active:scale-95"
                >
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>

        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Live preview foto
    document.getElementById('photoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran foto maksimal 2MB!');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(ev) {
            const preview = document.getElementById('photoPreview');
            const placeholder = document.getElementById('avatarPlaceholder');

            preview.src = ev.target.result;
            preview.classList.remove('hidden');
            preview.style.position = '';
            preview.style.top = '';
            preview.style.left = '';

            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });

    // Loading state saat submit
    document.getElementById('profileForm').addEventListener('submit', function() {
        const btn = document.getElementById('saveBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    });
</script>
@endpush
