@extends('layouts.app')

@section('content')

    <div class="container">
        <h2>Keranjang Belanja Anda</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if (empty($cartItems))
            <p>Keranjang Anda masih kosong.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Toko</th>
                        <th>Harga</th>
                        <th>Kuantitas</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cartItems as $item)
                        <tr>
                            <td>
                                <!-- Tampilkan gambar jika ada -->
                                <!-- <img src="{{ asset('storage/' . $item['variant']->gambar_varian) }}" width="50"> -->
                                {{ $item['variant']->product->nama_produk }}
                                <small>({{ $item['variant']->nama_varian }})</small>
                            </td>
                            <td>{{ $item['variant']->product->toko->toko_name }}</td>
                            <td>Rp {{ number_format($item['variant']->harga, 0, ',', '.') }}</td>
                            <td>
                                <!-- Form untuk update kuantitas -->
                                <form action="{{ route('cart.update', $item['variant']->id_varian) }}" method="POST">
                                    @csrf
                                    <input type="number" name="kuantitas" value="{{ $item['kuantitas'] }}" min="1"
                                        class="form-control" style="width: 70px; display: inline;">
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                            <td>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                            <td>
                                <!-- Form untuk hapus item -->
                                <form action="{{ route('cart.remove', $item['variant']->id_varian) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="text-right">
                <h3>Total: Rp {{ number_format($totalPrice, 0, ',', '.') }}</h3>
                {{-- Tombol ini mengarah ke halaman form checkout --}}
                <a href="{{ route('checkout.index') }}" class="btn btn-success">Lanjut ke Checkout</a>
            </div>
        @endif


    </div>
    @endcontent
