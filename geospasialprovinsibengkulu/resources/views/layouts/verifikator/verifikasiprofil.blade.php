@extends('layouts.verifikator.verifikatornav')
@section('title', 'Verifikasi Profil')
@section('page-title', 'Verifikasi Profil Produsen')

@push('styles')
<style>
    /* Premium Animation & Transitions */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .anim-fade-up {
        animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
    }
    .profile-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .profile-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -8px rgba(124, 58, 237, 0.15);
    }
    
    .diff-container {
        display: grid;
        grid-template-columns: 1fr 20px 1fr;
        gap: 12px;
        align-items: center;
    }
    @media (max-width: 768px) {
        .diff-container {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        .diff-arrow {
            transform: rotate(90deg);
            margin: 4px auto;
        }
    }
    
    .diff-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        height: 100%;
    }
    .diff-box.is-new {
        background: #fdf4ff;
        border-color: #f3e8ff;
    }
</style>
@endpush

@section('content')
<div class="bg-gradient-to-br from-indigo-50/50 via-white to-fuchsia-50/50 min-h-screen p-4 md:p-8">
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- Header Section --}}
        <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6 anim-fade-up" style="animation-delay: 0s;">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 tracking-tight">Perubahan Profil Instansi</h1>
                <p class="text-sm text-gray-500 mt-2 font-medium">Tinjau dan setujui pembaruan data profil yang diajukan oleh pengguna Produsen Data.</p>
            </div>
            <div class="flex items-center gap-3 bg-violet-50 px-4 py-3 rounded-xl border border-violet-100">
                <div class="w-10 h-10 rounded-full bg-violet-200 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-violet-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-gray-800">{{ $profiles->total() }} Permintaan</div>
                    <div class="text-xs text-gray-500 font-medium">Menunggu Verifikasi</div>
                </div>
            </div>
        </div>

        {{-- Session Alerts --}}
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl flex items-center gap-3 anim-fade-up" style="animation-delay: 0.1s;">
                <svg class="w-6 h-6 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl flex items-center gap-3 anim-fade-up" style="animation-delay: 0.1s;">
                <svg class="w-6 h-6 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-bold text-sm">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Verification List --}}
        <div class="space-y-5">
            @forelse($profiles as $i => $profile)
                @php
                    $pending = $profile->pending_data;
                    $user = $profile->user;
                @endphp
                <div class="profile-card bg-white rounded-2xl p-5 md:p-6 shadow-sm border border-gray-100 anim-fade-up" style="animation-delay: {{ 0.15 + ($i * 0.05) }}s;">
                    
                    <div class="flex flex-col md:flex-row gap-6">
                        {{-- Photo Review Section --}}
                        <div class="w-full md:w-48 shrink-0 flex flex-col gap-3">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Foto Profil</h3>
                            
                            @if(isset($pending['photo']) && $pending['photo'] != $profile->photo)
                                {{-- Has pending photo --}}
                                <div class="relative group rounded-xl overflow-hidden border border-gray-200">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent z-10 flex items-end p-2">
                                        <span class="text-[10px] font-bold text-white bg-violet-600 px-2 py-0.5 rounded-full">Baru</span>
                                    </div>
                                    <img src="{{ asset('storage/' . $pending['photo']) }}" alt="New Photo" class="w-full h-32 md:h-40 object-cover bg-gray-50">
                                </div>
                                <div class="flex justify-center -my-2.5 z-20 relative diff-arrow">
                                    <div class="bg-white rounded-full p-1 border border-gray-200 shadow-sm text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                    </div>
                                </div>
                                <div class="rounded-xl overflow-hidden border border-gray-200 relative opacity-60">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent z-10 flex items-end p-2">
                                        <span class="text-[10px] font-bold text-white bg-gray-500 px-2 py-0.5 rounded-full">Lama</span>
                                    </div>
                                    @if($profile->photo)
                                        <img src="{{ asset('storage/' . $profile->photo) }}" alt="Old Photo" class="w-full h-20 md:h-24 object-cover">
                                    @else
                                        <div class="w-full h-20 md:h-24 bg-gray-100 flex items-center justify-center text-gray-400 text-xs font-medium">Tidak ada foto</div>
                                    @endif
                                </div>
                            @else
                                {{-- No pending photo changes --}}
                                <div class="rounded-xl overflow-hidden border border-gray-200">
                                    @if($profile->photo)
                                        <img src="{{ asset('storage/' . $profile->photo) }}" alt="Current Photo" class="w-full h-32 md:h-40 object-cover bg-gray-50">
                                    @else
                                        <div class="w-full h-32 md:h-40 bg-gray-100 flex flex-col gap-2 items-center justify-center text-gray-400">
                                            <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span class="text-xs font-medium">Tidak ada foto</span>
                                        </div>
                                    @endif
                                    <div class="bg-gray-50 py-1.5 text-center border-t border-gray-200">
                                        <span class="text-[10px] font-bold text-gray-500">Tidak Ada Perubahan</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Text Data Review Section --}}
                        <div class="flex-1 flex flex-col">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Detail Informasi Instansi</h3>

                            <div class="space-y-4 flex-1">
                                {{-- Name --}}
                                @if(isset($pending['name']) && $pending['name'] !== $user->name)
                                    <div class="diff-container">
                                        <div class="diff-box">
                                            <div class="text-[10px] font-bold text-gray-400 uppercase mb-1">Nama Terdil (Lama)</div>
                                            <div class="text-sm font-semibold text-gray-600 line-through">{{ $user->name }}</div>
                                        </div>
                                        <div class="diff-arrow text-center text-violet-400">
                                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </div>
                                        <div class="diff-box is-new shadow-sm">
                                            <div class="text-[10px] font-bold text-violet-600 uppercase mb-1">Nama Baru</div>
                                            <div class="text-sm font-bold text-gray-800">{{ $pending['name'] }}</div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Instansi Name --}}
                                @if(isset($pending['instansi']) && $pending['instansi'] !== $profile->instansi)
                                    <div class="diff-container">
                                        <div class="diff-box">
                                            <div class="text-[10px] font-bold text-gray-400 uppercase mb-1">Instansi (Lama)</div>
                                            <div class="text-sm font-semibold text-gray-600 line-through">{{ $profile->instansi ?? '-' }}</div>
                                        </div>
                                        <div class="diff-arrow text-center text-violet-400">
                                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </div>
                                        <div class="diff-box is-new shadow-sm">
                                            <div class="text-[10px] font-bold text-violet-600 uppercase mb-1">Instansi Baru</div>
                                            <div class="text-sm font-bold text-gray-800">{{ $pending['instansi'] }}</div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Bio --}}
                                @if(isset($pending['bio']) && $pending['bio'] !== $profile->bio)
                                    <div class="diff-container">
                                        <div class="diff-box">
                                            <div class="text-[10px] font-bold text-gray-400 uppercase mb-1">Deskripsi / Bio (Lama)</div>
                                            <div class="text-sm text-gray-600 bg-gray-50 p-2 rounded border border-gray-100 line-through opacity-70">{{ $profile->bio ?? '-' }}</div>
                                        </div>
                                        <div class="diff-arrow text-center text-violet-400">
                                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </div>
                                        <div class="diff-box is-new shadow-sm">
                                            <div class="text-[10px] font-bold text-violet-600 uppercase mb-1">Deskripsi / Bio Baru</div>
                                            <div class="text-sm text-gray-800 bg-white p-2 rounded border border-violet-100">{{ $pending['bio'] }}</div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Contact Info --}}
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    @if(isset($pending['no_hp']) && $pending['no_hp'] !== $profile->no_hp)
                                        <div class="diff-box border-dashed border-gray-300">
                                            <div class="flex justify-between items-start mb-1">
                                                <div class="text-[10px] font-bold text-violet-600 uppercase flex items-center gap-1.5"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>No. HP Baru</div>
                                                <span class="text-[9px] font-bold text-gray-500 line-through">{{ $profile->no_hp ?? '-' }}</span>
                                            </div>
                                            <div class="text-sm font-bold text-gray-800">{{ $pending['no_hp'] }}</div>
                                        </div>
                                    @endif

                                    @if(isset($pending['alamat']) && $pending['alamat'] !== $profile->alamat)
                                        <div class="diff-box border-dashed border-gray-300">
                                            <div class="flex justify-between items-start mb-1">
                                                <div class="text-[10px] font-bold text-violet-600 uppercase flex items-center gap-1.5"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Alamat Baru</div>
                                                <span class="text-[9px] font-bold text-gray-500 line-through">{{ $profile->alamat ?? '-' }}</span>
                                            </div>
                                            <div class="text-sm font-bold text-gray-800">{{ $pending['alamat'] }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="mt-6 pt-5 border-t border-gray-100 flex flex-wrap md:flex-nowrap gap-3 items-center justify-end">
                                <form action="{{ route('verifikator.verifikasiprofil.process', $profile->profile_id) }}" method="POST" class="w-full md:w-auto">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="w-full md:w-auto flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl border border-red-200 text-red-600 font-bold bg-white hover:bg-red-50 hover:border-red-300 transition text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Tolak Perubahan
                                    </button>
                                </form>
                                <form action="{{ route('verifikator.verifikasiprofil.process', $profile->profile_id) }}" method="POST" class="w-full md:w-auto">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="w-full md:w-auto flex items-center justify-center gap-2 px-8 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 text-white font-bold hover:from-violet-700 hover:to-purple-700 shadow-lg shadow-violet-200 transition text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Setujui Perubahan
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl p-12 text-center border border-gray-100 shadow-sm anim-fade-up">
                    <div class="w-20 h-20 bg-gray-50 rounded-full mx-auto flex items-center justify-center mb-6 border border-gray-100">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Semua Sudah Ditinjau!</h3>
                    <p class="text-gray-500 max-w-sm mx-auto text-sm">Tidak ada permintaan perubahan profil baru dari Produsen Data saat ini. Anda akan melihat daftar tersebut di sini saat ada yang mengajukannya.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($profiles->hasPages())
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mt-6 anim-fade-up">
                {{ $profiles->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
