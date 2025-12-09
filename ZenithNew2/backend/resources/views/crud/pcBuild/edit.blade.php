@extends('layouts.app')

@php
    $detail = [];

    foreach ($build->buildDetail as $d) {
        $detail[$d->bagian_komponen]['id'] = [$d->id];
        $detail[$d->bagian_komponen]['produk'] = [$d->id_produk];
    }
@endphp

@section('content')
    <div class="container">
        <h2>Edit PC Build</h2>

        <form action="{{ route('dashboard.manage.pcBuild.update', $build) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <input type="hidden" name="id_user" value="{{ $user->id }}">

            <div class="mb-3">
                <label for="nama_build" class="form-label">Nama Build</label>
                <input type="text" name="nama_build" id="nama_build" class="form-control" value="{{ $build->nama_build }}"
                    required>
                @error('nama_build') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <h3>Komponen</h3>

            <div class="mb-3">
                <label for="motherboard" class="form-label">Motherboard</label>
                <input type="hidden" name="komponen[motherboard][id]" value="{{ $detail['motherboard']['id'][0] }}">
                <select name="komponen[motherboard][produk]" id="motherboard" class="form-select" required>
                    @foreach ($products as $p)
                        <option value="{{ $p->id_produk }}" {{ $p->id_produk === $detail['motherboard']['produk'][0] ? 'selected' : '' }}>
                            {{ $p->nama_produk }} ({{ $p->merek }})
                        </option>
                    @endforeach
                </select>
                @error('motherboard') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="cpu" class="form-label">CPU</label>
                <input type="hidden" name="komponen[cpu][id]" value="{{ $detail['cpu']['id'][0] }}">
                <select name="komponen[cpu][produk]" id="cpu" class="form-select" required>
                    @foreach ($products as $p)
                        <option value="{{ $p->id_produk }}" {{ $p->id_produk === $detail['cpu']['produk'][0] ? 'selected' : '' }}>
                            {{ $p->nama_produk }} ({{ $p->merek }})
                        </option>
                    @endforeach
                </select>
                @error('cpu') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="ram" class="form-label">RAM</label>
                <input type="hidden" name="komponen[ram][id]" value="{{ $detail['ram']['id'][0] }}">
                <select name="komponen[ram][produk]" id="ram" class="form-select" required>
                    @foreach ($products as $p)
                        <option value="{{ $p->id_produk }}" {{ $p->id_produk === $detail['ram']['produk'][0] ? 'selected' : '' }}>
                            {{ $p->nama_produk }} ({{ $p->merek }})
                        </option>
                    @endforeach
                </select>
                @error('ram') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="psu" class="form-label">Power Supply</label>
                <input type="hidden" name="komponen[psu][id]" value="{{ $detail['psu']['id'][0] }}">
                <select name="komponen[psu][produk]" id="psu" class="form-select" required>
                    @foreach ($products as $p)
                        <option value="{{ $p->id_produk }}" {{ $p->id_produk === $detail['psu']['produk'][0] ? 'selected' : '' }}>
                            {{ $p->nama_produk }} ({{ $p->merek }})
                        </option>
                    @endforeach
                </select>
                @error('psu') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="storage" class="form-label">Storage</label>
                <input type="hidden" name="komponen[storage][id]" value="{{ $detail['storage']['id'][0] }}">
                <select name="komponen[storage][produk]" id="storage" class="form-select" required>
                    @foreach ($products as $p)
                        <option value="{{ $p->id_produk }}" {{ $p->id_produk === $detail['storage']['produk'][0] ? 'selected' : '' }}>
                            {{ $p->nama_produk }} ({{ $p->merek }})
                        </option>
                    @endforeach
                </select>
                @error('storage') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="{{ route('dashboard.manage.pcBuild.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
@endsection