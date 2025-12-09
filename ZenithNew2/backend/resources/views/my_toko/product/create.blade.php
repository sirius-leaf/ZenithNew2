@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Tambah Produk Baru (Toko Saya)</h2>

        {{-- PERBAIKAN 1: Form action diubah --}}
        <form action="{{ route('dashboard.toko.produk.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="nama_produk" class="form-label">Nama Produk</label>
                <input type="text" name="nama_produk" id="nama_produk" class="form-control" value="{{ old('nama_produk') }}" required>
                @error('nama_produk') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="merek" class="form-label">Merek</label>
                <input type="text" name="merek" id="merek" class="form-control" value="{{ old('merek') }}" required>
                @error('merek') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="4" class="form-control">{{ old('deskripsi') }}</textarea>
                {{-- PERBAIKAN 3: Typo 'driver' diubah jadi 'deskripsi' --}}
                @error('deskripsi') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <button type="button" id="add-kategori-row" class="btn btn-secondary mb-3">Tambah Kategori</button>

                <div id="kategori-wrapper">
                    {{-- Baris pertama kategori --}}
                    <div class="input-group mb-2 kategori-item">
                        <div>
                            <label for="kategori_0" class="form-label">Kategori</label>
                            <select name="kategori[]" id="kategori_0" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($kategori as $k)
                                    <option value="{{ $k->id_kategori }}">{{ $k->nama_kategori }}</option>
                                @endforeach
                            </select>
                            @error('kategori.0') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <button type="button" class="btn btn-danger remove-kategori-row">Hapus</button>
                    </div>
                    {{-- (Tempat untuk baris kategori dinamis) --}}
                </div>
            </div>

            <div class="mb-3">
                <button type="button" id="add-varian-row" class="btn btn-secondary mb-3">Tambah Varian</button>

                <div id="varian-wrapper">
                    {{-- Baris pertama varian --}}
                    <div class="input-group mb-2 varian-item">
                        <div>
                            <label for="gambar_varian_0" class="form-label">Gambar Varian</label>
                            <input type="file" name="varian[0][gambar_varian]" id="gambar_varian_0" class="form-control" required>
                            @error('varian.0.gambar_varian') <small class="text-danger">{{ $message }}</small> @enderror

                            <label for="nama_varian_0" class="form-label">Nama Varian</label>
                            <input type="text" name="varian[0][nama_varian]" id="nama_varian_0" class="form-control" required>
                            @error('varian.0.nama_varian') <small class="text-danger">{{ $message }}</small> @enderror

                            <label for="harga_0" class="form-label">Harga</label>
                            <input type="number" min="0" name="varian[0][harga]" id="harga_0" class="form-control" required>
                            @error('varian.0.harga') <small class="text-danger">{{ $message }}</small> @enderror

                            <label for="stok_0" class="form-label">Stok</label>
                            <input type="number" min="0" name="varian[0][stok]" id="stok_0" class="form-control" required>
                            @error('varian.0.stok') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <button type="button" class="btn btn-danger remove-row">Hapus</button>
                    </div>
                    {{-- (Tempat untuk baris varian dinamis) --}}
                </div>
            </div>

            <button type="submit" class="btn btn-success">Simpan</button>
            {{-- PERBAIKAN 2: Tombol 'Kembali' diubah --}}
            <a href="{{ route('dashboard.toko.show') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
@endsection

{{--
  PERBAIKAN 4: JavaScript DIPERBARUI TOTAL
  Memperbaiki bug Blade @foreach di dalam string JS
--}}
@push('scripts')
<script>
    // --- ( PERBAIKAN BESAR PADA JAVASCRIPT ) ---

    // 1. Ubah data Kategori dari PHP (Blade) ke JavaScript
    const allKategori = @json($kategori);
    let kategoriOptionsHtml = '<option value="">-- Pilih Kategori --</option>';
    allKategori.forEach(k => {
        kategoriOptionsHtml += `<option value="${k.id_kategori}">${k.nama_kategori}</option>`;
    });

    // 2. Siapkan index untuk varian dan kategori
    let varianIndex = 1;
    let kategoriIndex = 1;

    // 3. Event listener untuk menambah varian
    document.getElementById('add-varian-row').addEventListener('click', function () {
        const wrapper = document.getElementById('varian-wrapper');
        const newRow = document.createElement('div');
        newRow.classList.add('input-group', 'mb-2', 'varian-item');

        // Buat ID unik untuk label dan input
        let i = varianIndex;

        newRow.innerHTML = `
            <div>
                <label for="gambar_varian_${i}" class="form-label">Gambar Varian</label>
                <input type="file" name="varian[${i}][gambar_varian]" id="gambar_varian_${i}" class="form-control" required>

                <label for="nama_varian_${i}" class="form-label">Nama Varian</label>
                <input type="text" name="varian[${i}][nama_varian]" id="nama_varian_${i}" class="form-control" required>

                <label for="harga_${i}" class="form-label">Harga</label>
                <input type="number" min="0" name="varian[${i}][harga]" id="harga_${i}" class="form-control" required>

                <label for="stok_${i}" class="form-label">Stok</label>
                <input type="number" min="0" name="varian[${i}][stok]" id="stok_${i}" class="form-control" required>
            </div>
            <button type="button" class="btn btn-danger remove-row">Hapus</button>
        `;
        varianIndex++;
        wrapper.appendChild(newRow);
    });

    // 4. Event listener untuk menambah kategori
    document.getElementById('add-kategori-row').addEventListener('click', function () {
        const wrapper = document.getElementById('kategori-wrapper');
        const newRow = document.createElement('div');
        newRow.classList.add('input-group', 'mb-2', 'kategori-item');

        let i = kategoriIndex;

        newRow.innerHTML = `
            <div>
                <label for="kategori_${i}" class="form-label">Kategori</label>
                <select name="kategori[]" id="kategori_${i}" class="form-select" required>
                    ${kategoriOptionsHtml}
                </select>
            </div>
            <button type="button" class="btn btn-danger remove-kategori-row">Hapus</button>
        `;
        kategoriIndex++;
        wrapper.appendChild(newRow);
    });

    // 5. Event listener untuk menghapus baris (varian atau kategori)
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.varian-item').remove();
        } else if (e.target.classList.contains('remove-kategori-row')) {
            e.target.closest('.kategori-item').remove();
        }
    });
</script>
@endpush
