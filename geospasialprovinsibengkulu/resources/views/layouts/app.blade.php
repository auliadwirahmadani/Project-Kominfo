{{--
|--------------------------------------------------------------------------
| APP LAYOUT — Normal Mode
|--------------------------------------------------------------------------
| Layout ini menggunakan geonav.blade.php sebagai base layout,
| namun dengan $navMode = 'normal':
|   - Navbar sticky (tidak floating)
|   - Tidak menampilkan search peta / filter layer
|   - Menampilkan footer
|   - Cocok untuk: catalog, about, dataset detail, dll.
|
| Cara pakai di child view:
|     @extends('layouts.app')
|     @section('title', 'Judul Halaman')
|     @section('content') ... @endsection
--}}

@extends('layouts.geonav', ['navMode' => 'normal'])
