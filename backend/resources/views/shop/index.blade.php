@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Selamat Datang di Toko Kami</h2>
    <div class="row">

        @forelse($products as $product)
            <div class="col-md-4 mb-4">
                <div class="card">
                    @php
                        // Ambil gambar dari varian pertama (jika ada)
                        $gambar = optional($product->variant->first())->gambar_varian;
                        $harga = optional($product->variant->first())->harga;
                    @endphp

                    @if($gambar)
                        <img src="{{ asset('storage/' . $gambar) }}" class="card-img-top" alt="{{ $product->nama_produk }}">
                    @else
                        <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Gambar tidak tersedia">
                    @endif

                    <div class="card-body">
                        <h5 class="card-title">{{ $product->nama_produk }}</h5>
                        <p class="card-text">{{ $product->merek }}</p>
                        <p class="card-text">
                            @if($harga)
                                Mulai dari Rp {{ number_format($harga, 0, ',', '.') }}
                            @else
                                Harga tidak tersedia
                            @endif
                        </p>

                        <a href="{{ route('shop.show', $product->id_produk) }}" class="btn btn-primary">
                            Lihat Detail & Varian
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p>Belum ada produk yang tersedia saat ini.</p>
            </div>
        @endforelse

    </div>

    <div class="d-flex justify-content-center">
        {{ $products->links() }}
    </div>
</div>
@endsection
