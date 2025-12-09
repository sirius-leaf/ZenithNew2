@extends('layouts.app')

@section('content')
    <h1>Kelola Toko: {{ $toko->toko_name }}</h1>
    <p>{{ $toko->deskripsi }}</p>

    <!-- Link edit toko (jika ada) -->
    <!-- <a href="{{ route('dashboard.manage.toko.edit', $toko->id) }}">Edit Info Toko</a> -->

    <hr>

    @include('crud.produk.index')

    {{-- <h2>Produk Anda</h2>

    <!--
                  Tombol untuk Tambah Produk (Arahkan ke rute baru)
                  [PERBAIKAN 1]: Mengarah ke 'produk.create'
                -->
    <a href="{{ route('dashboard.toko.produk.create') }}"
        style="background: blue; color: white; padding: 5px 10px; text-decoration: none;">
        + Tambah Produk Baru
    </a>

    <br><br>

    <!-- Daftar Produk yang Sudah Ada -->
    <table border="1" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Merek</th>
                <th>Varian</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($toko->products as $product)
            <tr>
                <td>{{ $product->nama_produk }}</td>
                <td>{{ $product->merek }}</td>
                <td>
                    <!-- Anda bisa loop varian di sini jika mau -->
                    {{ $product->variant->count() }} Varian
                </td>
                <td>
                    <a href="{{ route('dashboard.toko.produk.edit', $product->id_produk) }}">Edit</a>

                    <form action="{{ route('dashboard.toko.produk.destroy', $product->id_produk) }}" method="POST"
                        style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Yakin ingin hapus?')">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center;">
                    Anda belum memiliki produk.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table> --}}
@endsection