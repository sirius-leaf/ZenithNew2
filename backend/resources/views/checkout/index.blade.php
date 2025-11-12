@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Checkout</h2>

        @if(session('error'))
        <div class="alert alert-danger">
            <strong>Error:</strong> {{ session('error') }}
        </div>
    @endif

        <div class="row">
            <div class="col-md-7">
                <h4>Form Pengiriman</h4>
                <form action="{{ route('checkout.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="nama">Nama Penerima</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" value="{{ Auth::user()->email }}" readonly>
                    </div>

                    <div class="form-group">
                        <label for="alamat_pengiriman">Alamat Pengiriman Lengkap</label>
                        <textarea name="alamat_pengiriman" id="alamat_pengiriman" rows="4" class="form-control" required>{{ old('alamat_pengiriman') }}</textarea>
                        @error('alamat_pengiriman')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <hr>
                    <p>Metode Pembayaran (Contoh: Transfer Bank)</p>
                    {{-- Di sini Anda bisa tambahkan pilihan metode pembayaran jika perlu --}}

                    <button type="submit" class="btn btn-primary">Buat Pesanan</button>
                </form>
            </div>

            <div class="col-md-5">
                <h4>Ringkasan Pesanan</h4>
                <table class="table">
                    @foreach ($cartItems as $item)
                        <tr>
                            <td>{{ $item['nama'] }} (x{{ $item['kuantitas'] }})</td>
                        </tr>
                    @endforeach
                </table>
                <hr>
                <h5>Total: Rp {{ number_format($totalPrice, 0, ',', '.') }}</h5>
            </div>
        </div>


    </div>
@endsection
