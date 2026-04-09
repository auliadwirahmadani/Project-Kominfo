@extends('layouts.verifikator.verifikatornav')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Verifikator')

{{-- Section Content --}}
@push('styles')
<style>
    /* =============================================
       MONITORING STATUS — DESIGN SYSTEM
    ============================================= */
    .stat-card {
        background: white;
        border-radius: 18px;
        padding: 1.5rem;
        border: 1px solid #f1f5f9;
        box-shadow: 0 2px 12px -2px rgba(0,0,0,0.06);
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
        overflow: hidden;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px -4px rgba(0,0,0,0.1); }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 18px 18px 0 0;
    }
    .stat-card.pending::before  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .stat-card.approved::before { background: linear-gradient(90deg, #10b981, #34d399); }
    .stat-card.rejected::before { background: linear-gradient(90deg, #ef4444, #f87171); }
    .stat-card.published::before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .stat-card.total::before    { background: linear-gradient(90deg, #6366f1, #818cf8); }

    /* Progress Bar */
    .progress-bar { height: 6px; border-radius: 99px; background: #f1f5f9; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 99px; transition: width 0.8s cubic-bezier(0.4,0,0.2,1); }

    /* Status Badge */
    .badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 700;
    }
    .badge.pending  { background: #fef3c7; color: #92400e; }
    .badge.approved { background: #d1fae5; color: #065f46; }
    .badge.rejected { background: #fee2e2; color: #991b1b; }

    /* Table */
    .mon-table { width: 100%; border-collapse: collapse; }
    .mon-table thead th {
        padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;
        background: #f8fafc; border-bottom: 1px solid #f1f5f9;
    }
    .mon-table thead th:first-child { border-radius: 12px 0 0 0; }
    .mon-table thead th:last-child  { border-radius: 0 12px 0 0; }
    .mon-table tbody td {
        padding: 13px 16px; font-size: 13px; color: #374151;
        border-bottom: 1px solid #f8fafc; vertical-align: middle;
    }
    .mon-table tbody tr:last-child td { border-bottom: none; }
    .mon-table tbody tr:hover td { background: #f8fafc; }
    .mon-table tbody tr { transition: background 0.15s; }

    /* Timeline */
    .timeline-item { display: flex; gap: 14px; padding: 12px 0; position: relative; }
    .timeline-item + .timeline-item::before {
        content: '';
        position: absolute; left: 14px; top: -1px;
        width: 2px; height: 14px; background: #e5e7eb;
    }
    .timeline-dot {
        width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; margin-top: 2px;
    }

    /* Tab Buttons */
    .tab-btn {
        display: flex; align-items: center; gap: 6px;
        padding: 8px 18px; border-radius: 10px; font-size: 13px; font-weight: 600;
        transition: all 0.2s; cursor: pointer; border: 2px solid transparent;
        color: #6b7280; background: transparent;
    }
    .tab-btn.active { background: white; color: #4f46e5; border-color: #e0e7ff; box-shadow: 0 2px 8px -2px rgba(79,70,229,0.15); }
    .tab-btn:not(.active):hover { background: white/50; color: #374151; }

    /* Row number */
    .row-num { color: #9ca3af; font-size: 11px; font-weight: 600; font-family: monospace; }

    /* Publish dot */
    .pub-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }

    /* Search/Filter bar */
    .filter-input {
        padding: 9px 14px; border: 1px solid #e5e7eb; border-radius: 10px;
        font-size: 13px; color: #374151; outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: white;
    }
    .filter-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }

    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in { animation: fadeSlideIn 0.4s ease-out both; }
    .anim-delay-1 { animation-delay: 0.05s; }
    .anim-delay-2 { animation-delay: 0.10s; }
    .anim-delay-3 { animation-delay: 0.15s; }
    .anim-delay-4 { animation-delay: 0.20s; }
    .anim-delay-5 { animation-delay: 0.25s; }
</style>
@endpush

@section('content')
@php
    $pct = fn($n) => $totalAll > 0 ? round(($n / $totalAll) * 100) : 0;
    $currentStatus = request('status', '');
    $currentSearch = request('search', '');
@endphp

<div class="bg-gradient-to-br from-slate-50 via-indigo-50/30 to-blue-50 min-h-screen p-4 md:p-8">
<div class="max-w-7xl mx-auto space-y-6">

    {{-- ================================================================
         HEADER
    ================================================================ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 animate-fade-in">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Monitoring Status</h1>
            </div>
            <p class="text-sm text-gray-500 ml-13">Pantau status unggah dataset geospasial dan riwayat verifikasi secara real-time.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400 font-medium">Terakhir diperbarui:</span>
            <span class="text-xs font-bold text-gray-600">{{ now()->format('d M Y, H:i') }} WIB</span>
            <a href="{{ route('verifikator.dashboard') }}" class="ml-2 p-2 rounded-lg hover:bg-indigo-50 text-indigo-500 transition" title="Refresh">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </a>
        </div>
    </div>

    {{-- ================================================================
         STAT CARDS
    ================================================================ --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">

        {{-- Total --}}
        <div class="stat-card total animate-fade-in col-span-2 lg:col-span-1">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <span class="text-3xl font-black text-indigo-600">{{ $totalAll }}</span>
            </div>
            <p class="text-sm font-bold text-gray-700">Total Dataset</p>
            <p class="text-xs text-gray-400 mt-0.5">Semua geospasial yang terunggah</p>
            <div class="progress-bar mt-3">
                <div class="progress-fill bg-indigo-400" style="width: 100%"></div>
            </div>
        </div>

        {{-- Pending --}}
        <div class="stat-card pending animate-fade-in anim-delay-1">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-3xl font-black text-amber-500">{{ $totalPending }}</span>
            </div>
            <p class="text-sm font-bold text-gray-700">Menunggu</p>
            <p class="text-xs text-gray-400 mt-0.5">Belum diverifikasi</p>
            <div class="progress-bar mt-3">
                <div class="progress-fill bg-amber-400" style="width: {{ $pct($totalPending) }}%"></div>
            </div>
            <p class="text-right text-[11px] text-amber-500 font-bold mt-1">{{ $pct($totalPending) }}%</p>
        </div>

        {{-- Approved --}}
        <div class="stat-card approved animate-fade-in anim-delay-2">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-3xl font-black text-emerald-500">{{ $totalApproved }}</span>
            </div>
            <p class="text-sm font-bold text-gray-700">Disetujui</p>
            <p class="text-xs text-gray-400 mt-0.5">Lulus verifikasi</p>
            <div class="progress-bar mt-3">
                <div class="progress-fill bg-emerald-400" style="width: {{ $pct($totalApproved) }}%"></div>
            </div>
            <p class="text-right text-[11px] text-emerald-500 font-bold mt-1">{{ $pct($totalApproved) }}%</p>
        </div>

        {{-- Rejected --}}
        <div class="stat-card rejected animate-fade-in anim-delay-3">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-3xl font-black text-red-500">{{ $totalRejected }}</span>
            </div>
            <p class="text-sm font-bold text-gray-700">Ditolak</p>
            <p class="text-xs text-gray-400 mt-0.5">Tidak lolos verifikasi</p>
            <div class="progress-bar mt-3">
                <div class="progress-fill bg-red-400" style="width: {{ $pct($totalRejected) }}%"></div>
            </div>
            <p class="text-right text-[11px] text-red-500 font-bold mt-1">{{ $pct($totalRejected) }}%</p>
        </div>

        {{-- Published --}}
        <div class="stat-card published animate-fade-in anim-delay-4">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-3xl font-black text-blue-500">{{ $totalPublished }}</span>
            </div>
            <p class="text-sm font-bold text-gray-700">Dipublikasi</p>
            <p class="text-xs text-gray-400 mt-0.5">Tersedia di katalog</p>
            <div class="progress-bar mt-3">
                <div class="progress-fill bg-blue-400" style="width: {{ $pct($totalPublished) }}%"></div>
            </div>
            <p class="text-right text-[11px] text-blue-500 font-bold mt-1">{{ $pct($totalPublished) }}%</p>
        </div>
    </div>

    {{-- ================================================================
         MAIN CONTENT — TAB LAYOUT
    ================================================================ --}}
    <div x-data="{ activeTab: '{{ $currentStatus || $currentSearch ? 'table' : 'table' }}' }" class="animate-fade-in anim-delay-5">

        {{-- Tab Buttons --}}
        <div class="flex gap-2 mb-4 bg-gray-100/70 p-1.5 rounded-xl w-fit">
            <button @click="activeTab = 'table'" :class="activeTab === 'table' ? 'active' : ''" class="tab-btn">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M10 6h4m-4 12h4M4 6h.01M4 14h.01M4 18h.01M4 10h.01M20 6h.01M20 14h.01M20 18h.01M20 10h.01"/></svg>
                Status Unggah
                @if($totalPending > 0)
                <span class="ml-1 px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-[10px] font-black">{{ $totalPending }}</span>
                @endif
            </button>
            <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'active' : ''" class="tab-btn">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Riwayat Verifikasi
            </button>
        </div>

        {{-- ============================================================
             TAB 1 — TABEL STATUS UNGGAH
        ============================================================ --}}
        <div x-show="activeTab === 'table'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">

            {{-- Filter Bar --}}
            <form method="GET" action="{{ route('verifikator.dashboard') }}" id="monFilterForm">
            <div class="flex flex-wrap items-center gap-3 bg-white border border-gray-100 rounded-2xl p-4 shadow-sm mb-4">

                {{-- Search --}}
                <div class="flex-1 min-w-[200px] relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ $currentSearch }}"
                           placeholder="Cari nama dataset..."
                           class="filter-input pl-9 w-full"
                           onchange="document.getElementById('monFilterForm').submit()">
                </div>

                {{-- Status Filter --}}
                <div class="flex gap-2 flex-wrap">
                    @foreach(['' => 'Semua', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $val => $label)
                    <a href="{{ route('verifikator.dashboard', array_merge(request()->only('search'), ['status' => $val])) }}"
                       class="px-3 py-2 rounded-lg text-xs font-bold transition border
                              {{ $currentStatus === $val
                                  ? ($val === 'pending' ? 'bg-amber-100 text-amber-800 border-amber-200'
                                    : ($val === 'approved' ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                                    : ($val === 'rejected' ? 'bg-red-100 text-red-800 border-red-200'
                                    : 'bg-indigo-100 text-indigo-800 border-indigo-200')))
                                  : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100' }}">
                        {{ $label }}
                        @if($val === 'pending') <span class="ml-1">({{ $totalPending }})</span>
                        @elseif($val === 'approved') <span class="ml-1">({{ $totalApproved }})</span>
                        @elseif($val === 'rejected') <span class="ml-1">({{ $totalRejected }})</span>
                        @else <span class="ml-1">({{ $totalAll }})</span>
                        @endif
                    </a>
                    @endforeach
                </div>

                @if($currentSearch || $currentStatus)
                <a href="{{ route('verifikator.dashboard') }}" class="flex items-center gap-1 px-3 py-2 text-xs text-gray-500 hover:text-red-600 bg-gray-50 border border-gray-200 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset
                </a>
                @endif
            </div>
            </form>

            {{-- Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="mon-table">
                        <thead>
                            <tr>
                                <th class="w-10">#</th>
                                <th>Nama Dataset</th>
                                <th>Kategori</th>
                                <th>Metadata</th>
                                <th>Status Verifikasi</th>
                                <th>Publikasi</th>
                                <th>Tanggal Unggah</th>
                                <th>Terakhir Diperbarui</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($layers as $i => $layer)
                            @php
                                $status = $layer->status_verifikasi;
                                $rowNum = ($layers->currentPage() - 1) * $layers->perPage() + $i + 1;
                            @endphp
                            <tr>
                                <td><span class="row-num">{{ $rowNum }}</span></td>

                                {{-- Nama Dataset --}}
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                            {{ $status === 'approved' ? 'bg-emerald-100' : ($status === 'rejected' ? 'bg-red-100' : 'bg-amber-100') }}">
                                            <svg class="w-4 h-4 {{ $status === 'approved' ? 'text-emerald-600' : ($status === 'rejected' ? 'text-red-600' : 'text-amber-600') }}"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800 text-sm leading-tight max-w-[200px] truncate" title="{{ $layer->layer_name }}">
                                                {{ $layer->layer_name }}
                                            </p>
                                            <p class="text-[11px] text-gray-400 font-mono">ID: {{ $layer->geospatial_id }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kategori --}}
                                <td>
                                    <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg">
                                        {{ $layer->category->category_name ?? 'Tanpa Kategori' }}
                                    </span>
                                </td>

                                {{-- Metadata --}}
                                <td>
                                    @if($layer->metadata)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded-lg">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Lengkap
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-1 rounded-lg">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Belum diisi
                                        </span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td>
                                    @if($status === 'approved')
                                        <span class="badge approved">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Disetujui
                                        </span>
                                    @elseif($status === 'rejected')
                                        <span class="badge rejected">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="badge pending">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Menunggu
                                        </span>
                                    @endif
                                </td>

                                {{-- Publikasi --}}
                                <td>
                                    <div class="flex items-center gap-2">
                                        <span class="pub-dot {{ $layer->is_published ? 'bg-blue-400' : 'bg-gray-300' }}"></span>
                                        <span class="text-xs font-medium {{ $layer->is_published ? 'text-blue-600' : 'text-gray-400' }}">
                                            {{ $layer->is_published ? 'Dipublikasi' : 'Belum' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Tanggal Unggah --}}
                                <td>
                                    <span class="text-xs text-gray-600">
                                        {{ $layer->created_at->format('d M Y') }}
                                    </span>
                                    <br>
                                    <span class="text-[11px] text-gray-400">
                                        {{ $layer->created_at->format('H:i') }} WIB
                                    </span>
                                </td>

                                {{-- Terakhir diperbarui --}}
                                <td>
                                    <span class="text-xs text-gray-500">{{ $layer->updated_at->diffForHumans() }}</span>
                                </td>

                                {{-- Aksi --}}
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('verifikator.geospasial.index') }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition text-xs font-bold"
                                           title="Verifikasi Geospasial">
                                            <span>Geo</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                        <a href="{{ route('verifikator.metadata.index') }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-violet-50 text-violet-600 hover:bg-violet-100 transition text-xs font-bold"
                                           title="Verifikasi Metadata">
                                            <span>Meta</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9">
                                    <div class="py-16 text-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <h3 class="text-base font-bold text-gray-600">Tidak ada data ditemukan</h3>
                                        <p class="text-sm text-gray-400 mt-1">Coba ubah filter atau kata kunci pencarian.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($layers->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-xs text-gray-500">
                        Menampilkan <span class="font-bold text-gray-700">{{ $layers->firstItem() }}–{{ $layers->lastItem() }}</span>
                        dari <span class="font-bold text-gray-700">{{ $layers->total() }}</span> dataset
                    </p>
                    <div class="text-sm">{{ $layers->links() }}</div>
                </div>
                @else
                <div class="px-5 py-3 border-t border-gray-100">
                    <p class="text-xs text-gray-400">Total <span class="font-bold text-gray-600">{{ $layers->total() }}</span> dataset</p>
                </div>
                @endif
            </div>
        </div>

        {{-- ============================================================
             TAB 2 — RIWAYAT VERIFIKASI (TIMELINE)
        ============================================================ --}}
        <div x-show="activeTab === 'history'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             style="display:none;">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Donut Chart Visual (Manual CSS) --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 text-sm mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                        Distribusi Status
                    </h3>

                    {{-- Visual: stacked bar --}}
                    @php $total = max($totalAll, 1); @endphp
                    <div class="rounded-xl overflow-hidden h-5 w-full flex mb-4">
                        @if($totalApproved > 0)
                        <div class="h-full bg-emerald-400 transition-all" style="width: {{ ($totalApproved/$total)*100 }}%" title="Disetujui: {{ $totalApproved }}"></div>
                        @endif
                        @if($totalPending > 0)
                        <div class="h-full bg-amber-400 transition-all" style="width: {{ ($totalPending/$total)*100 }}%" title="Menunggu: {{ $totalPending }}"></div>
                        @endif
                        @if($totalRejected > 0)
                        <div class="h-full bg-red-400 transition-all" style="width: {{ ($totalRejected/$total)*100 }}%" title="Ditolak: {{ $totalRejected }}"></div>
                        @endif
                        @if($totalAll === 0)
                        <div class="h-full bg-gray-200 w-full"></div>
                        @endif
                    </div>

                    {{-- Legend --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-sm bg-emerald-400 flex-shrink-0"></span>
                                <span class="text-sm text-gray-600">Disetujui</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-gray-800">{{ $totalApproved }}</span>
                                <span class="text-xs text-gray-400">({{ $pct($totalApproved) }}%)</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-sm bg-amber-400 flex-shrink-0"></span>
                                <span class="text-sm text-gray-600">Menunggu</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-gray-800">{{ $totalPending }}</span>
                                <span class="text-xs text-gray-400">({{ $pct($totalPending) }}%)</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-sm bg-red-400 flex-shrink-0"></span>
                                <span class="text-sm text-gray-600">Ditolak</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-gray-800">{{ $totalRejected }}</span>
                                <span class="text-xs text-gray-400">({{ $pct($totalRejected) }}%)</span>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-3 flex items-center justify-between">
                            <span class="text-sm font-bold text-gray-700">Total Dataset</span>
                            <span class="text-lg font-black text-indigo-600">{{ $totalAll }}</span>
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="bg-blue-50 rounded-xl p-3 text-center">
                            <p class="text-2xl font-black text-blue-600">{{ $totalPublished }}</p>
                            <p class="text-[11px] font-semibold text-blue-500 mt-0.5">Dipublikasi</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3 text-center">
                            <p class="text-2xl font-black text-gray-600">{{ $totalAll - $totalPublished }}</p>
                            <p class="text-[11px] font-semibold text-gray-500 mt-0.5">Belum Publish</p>
                        </div>
                    </div>
                </div>

                {{-- Timeline Riwayat --}}
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 text-sm mb-5 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Riwayat Aktivitas Terbaru
                        <span class="ml-auto text-[11px] text-gray-400 font-normal">20 aktivitas terakhir</span>
                    </h3>

                    <div class="divide-y divide-gray-50 max-h-[520px] overflow-y-auto pr-1 custom-timeline-scroll">
                        @forelse($recentActivity as $item)
                        @php
                            $s = $item->status_verifikasi;
                            $dotClass = $s === 'approved' ? 'bg-emerald-400' : ($s === 'rejected' ? 'bg-red-400' : 'bg-amber-400');
                            $iconBg   = $s === 'approved' ? 'bg-emerald-50' : ($s === 'rejected' ? 'bg-red-50' : 'bg-amber-50');
                            $iconColor= $s === 'approved' ? 'text-emerald-600' : ($s === 'rejected' ? 'text-red-600' : 'text-amber-600');
                        @endphp
                        <div class="timeline-item">
                            {{-- Icon --}}
                            <div class="timeline-dot {{ $iconBg }}">
                                @if($s === 'approved')
                                <svg class="w-3.5 h-3.5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @elseif($s === 'rejected')
                                <svg class="w-3.5 h-3.5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                <svg class="w-3.5 h-3.5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0 pb-2">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 truncate" title="{{ $item->layer_name }}">
                                            {{ $item->layer_name }}
                                        </p>
                                        <div class="flex flex-wrap items-center gap-2 mt-1">
                                            <span class="badge {{ $s }} text-[10px]">
                                                {{ $s === 'approved' ? 'Disetujui' : ($s === 'rejected' ? 'Ditolak' : 'Menunggu') }}
                                            </span>
                                            @if($item->category)
                                            <span class="text-[11px] text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded-full font-medium">
                                                {{ $item->category->category_name }}
                                            </span>
                                            @endif
                                            @if($item->is_published)
                                            <span class="text-[11px] text-blue-500 bg-blue-50 px-2 py-0.5 rounded-full font-medium">
                                                📡 Dipublikasi
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-[11px] text-gray-500 whitespace-nowrap">{{ $item->updated_at->diffForHumans() }}</p>
                                        <p class="text-[10px] text-gray-400 font-mono mt-0.5">{{ $item->updated_at->format('d/m H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="py-16 text-center">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm font-semibold text-gray-500">Belum ada aktivitas verifikasi</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Quick Action Buttons --}}
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('verifikator.geospasial.index') }}"
                   class="flex items-center gap-4 p-4 bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-md hover:border-indigo-200 transition group">
                    <div class="w-12 h-12 bg-indigo-100 group-hover:bg-indigo-200 rounded-xl flex items-center justify-center shrink-0 transition">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800 text-sm">Periksa Geospasial</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $totalPending }} menunggu verifikasi</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 ml-auto group-hover:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('verifikator.metadata.index') }}"
                   class="flex items-center gap-4 p-4 bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-md hover:border-violet-200 transition group">
                    <div class="w-12 h-12 bg-violet-100 group-hover:bg-violet-200 rounded-xl flex items-center justify-center shrink-0 transition">
                        <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800 text-sm">Periksa Metadata</p>
                        <p class="text-xs text-gray-500 mt-0.5">Kelengkapan data deskriptif</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 ml-auto group-hover:text-violet-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('verifikator.dashboard') }}"
                   class="flex items-center gap-4 p-4 bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-md hover:border-blue-200 transition group">
                    <div class="w-12 h-12 bg-blue-100 group-hover:bg-blue-200 rounded-xl flex items-center justify-center shrink-0 transition">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800 text-sm">Dashboard</p>
                        <p class="text-xs text-gray-500 mt-0.5">Kembali ke ringkasan utama</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 ml-auto group-hover:text-blue-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

    </div>{{-- end tab container --}}

</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Animate progress bars on load
    document.querySelectorAll('.progress-fill').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => { bar.style.width = width; }, 200);
    });
});

// Scrollbar style for timeline
const styleEl = document.createElement('style');
styleEl.textContent = `
    .custom-timeline-scroll { scrollbar-width: thin; scrollbar-color: #e0e7ff #f8fafc; }
    .custom-timeline-scroll::-webkit-scrollbar { width: 5px; }
    .custom-timeline-scroll::-webkit-scrollbar-track { background: #f8fafc; border-radius: 3px; }
    .custom-timeline-scroll::-webkit-scrollbar-thumb { background: #c7d2fe; border-radius: 3px; }
`;
document.head.appendChild(styleEl);
</script>
@endpush