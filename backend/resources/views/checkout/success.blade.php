@extends('layouts.app')

@section('content')
    <div class="container text-center">
        <h2>Pesanan Berhasil!</h2>
        <p>Terima kasih atas pesanan Anda. Pesanan Anda sedang kami proses.</p>
        <a href="{{ route('home') }}" class="btn btn-primary">Kembali ke Beranda</a>
        {{-- Tambahkan link ke halaman 'Daftar Pesanan Saya' --}}
    </div>
@endsection
