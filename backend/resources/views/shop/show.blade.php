@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6">
            @php
                // Ambil gambar dari varian pertama untuk ditampilkan besar
                $gambar = optional($product->variant->first())->gambar_varian;
            @endphp
            @if($gambar)
                <img src="{{ asset('storage/' . $gambar) }}" class="img-fluid" alt="{{ $product->nama_produk }}">
            @else
                <img src="https://via.placeholder.com/400x300" class="img-fluid" alt="Gambar tidak tersedia">
            @endif
        </div>

        <div class="col-md-6">
            <h2>{{ $product->nama_produk }}</h2>
            <p class="text-muted">Merek: {{ $product->merek }}</p>
            <p>{{ $product->deskripsi }}</p>

            <hr>
            <h4>Pilih Varian:</h4>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @forelse($product->variant as $variant)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $variant->nama_varian }}</strong><br>
                                <span class="text-success">Rp {{ number_format($variant->harga, 0, ',', '.') }}</span><br>
                                <small>Stok: {{ $variant->stok }}</small>
                            </div>

                            <form action="{{ route('cart.add', $variant->id_varian) }}" method="POST">
                                @csrf

                                @if($variant->stok > 0)
                                    <div class="input-group">
                                        <input type="number" name="kuantitas" value="1" min="1" max="{{ $variant->stok }}" class="form-control" style="width: 70px;">
                                        <button type="submit" class="btn btn-success">
                                            + Keranjang
                                        </button>
                                    </div>
                                @else
                                    <span class="badge bg-danger">Stok Habis</span>
                                @endif
                            </form>

                        </div>
                    </div>
                </div>
            @empty
                <p>Varian untuk produk ini belum tersedia.</p>
            @endforelse

        </div>
    </div>
</div>
@endsection
