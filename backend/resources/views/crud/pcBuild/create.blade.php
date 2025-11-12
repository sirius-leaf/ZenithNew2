@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Tambah PC Build Baru</h2>

        <form action="{{ route('dashboard.manage.pcBuild.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="id_user" value="{{ $user->id }}">

            <div class="mb-3">
                <label for="nama_build" class="form-label">Nama Build</label>
                <input type="text" name="nama_build" id="nama_build" class="form-control" required>
                @error('nama_build') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <h3>Komponen</h3>

            <div class="mb-3">
                <label for="motherboard" class="form-label">Motherboard</label>
                <select name="komponen[motherboard]" id="motherboard" class="form-select" required>
                    <option value="">-- Pilih Komponen --</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id_produk }}">{{ $p->nama_produk }} ({{ $p->merek }})</option>
                    @endforeach
                </select>
                @error('motherboard') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="cpu" class="form-label">CPU</label>
                <select name="komponen[cpu]" id="cpu" class="form-select" required>
                    <option value="">-- Pilih Komponen --</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id_produk }}">{{ $p->nama_produk }} ({{ $p->merek }})</option>
                    @endforeach
                </select>
                @error('cpu') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="ram" class="form-label">RAM</label>
                <select name="komponen[ram]" id="ram" class="form-select" required>
                    <option value="">-- Pilih Komponen --</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id_produk }}">{{ $p->nama_produk }} ({{ $p->merek }})</option>
                    @endforeach
                </select>
                @error('ram') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="psu" class="form-label">Power Supply</label>
                <select name="komponen[psu]" id="psu" class="form-select" required>
                    <option value="">-- Pilih Komponen --</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id_produk }}">{{ $p->nama_produk }} ({{ $p->merek }})</option>
                    @endforeach
                </select>
                @error('psu') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="storage" class="form-label">Storage</label>
                <select name="komponen[storage]" id="storage" class="form-select" required>
                    <option value="">-- Pilih Komponen --</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id_produk }}">{{ $p->nama_produk }} ({{ $p->merek }})</option>
                    @endforeach
                </select>
                @error('storage') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="{{ route('dashboard.manage.pcBuild.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
@endsection