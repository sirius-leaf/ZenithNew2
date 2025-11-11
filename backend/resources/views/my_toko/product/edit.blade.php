@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Produk (Toko Saya)</h2>

        {{-- PERBAIKAN 1: Form action diubah & $produk->id_produk digunakan --}}
        <form action="{{ route('dashboard.toko.produk.update', $produk->id_produk) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="nama_produk" class="form-label">Nama Produk</label>
                <input type="text" name="nama_produk" id="nama_produk" class="form-control"
                    value="{{ old('nama_produk', $produk->nama_produk) }}" required>
                @error('nama_produk') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="merek" class="form-label">Merek</label>
                <input type="text" name="merek" id="merek" class="form-control"
                    value="{{ old('merek', $produk->merek) }}" required>
                @error('merek') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="4"
                    class="form-control">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                {{-- PERBAIKAN 3: Typo 'driver' diubah jadi 'deskripsi' --}}
                @error('deskripsi') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <button type="button" id="add-kategori-row" class="btn btn-secondary mb-3">Tambah Kategori</button>

                <div id="kategori-wrapper">
                    {{-- Loop untuk kategori yang sudah ada --}}
                    @foreach ($produk->categoryDetail as $i => $detail)
                        <div class="input-group mb-2 kategori-item">
                            <div>
                                <input type="hidden" name="detail[{{ $i }}][id]" value="{{ $detail->id }}">

                                <label for="kategori_{{ $i }}" class="form-label">Kategori</label>
                                <select name="detail[{{ $i }}][kategori]" id="kategori_{{ $i }}"
                                    class="form-select" required>
                                    @foreach ($kategori as $k)
                                        <option value="{{ $k->id_kategori }}"
                                            {{ $detail->id_kategori == $k->id_kategori ? 'selected' : '' }}>
                                            {{ $k->nama_kategori }}</option>
                                    @endforeach
                                </select>
                                @error("detail.$i.kategori") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <button type="button" class="btn btn-danger remove-kategori-row">Hapus</button>
                        </div>
                    @endforeach
                    {{-- (Tempat untuk baris kategori dinamis) --}}
                </div>
            </div>

            <div class="mb-3">
                <button type="button" id="add-varian-row" class="btn btn-secondary mb-3">Tambah Varian</button>

                <div id="varian-wrapper">
                    {{-- Loop untuk varian yang sudah ada --}}
                    @foreach ($produk->variant as $i => $varian)
                        <div class="input-group mb-2 varian-item">
                            <div>
                                <label for="gambar_varian_{{ $i }}" class="form-label">Gambar Sebelumnya</label><br>
                                <img src="{{ asset('storage/' . $varian->gambar_varian) }}" width="300">
                                <br>

                                <input type="hidden" name="varian[{{ $i }}][id_varian]"
                                    value="{{ $varian->id_varian }}">

                                <label for="gambar_varian_{{ $i }}" class="form-label">Ubah Gambar Varian</label>
                                <input type="file" name="varian[{{ $i }}][gambar_varian]"
                                    id="gambar_varian_{{ $i }}" class="form-control">
                                @error("varian.$i.gambar_varian") <small class="text-danger">{{ $message }}</small> @enderror

                                <label for="nama_varian_{{ $i }}" class="form-label">Nama Varian</label>
                                <input type="text" name="varian[{{ $i }}][nama_varian]"
                                    id="nama_varian_{{ $i }}" class="form-control"
                                    value="{{ old("varian.$i.nama_varian", $varian->nama_varian) }}" required>
                                @error("varian.$i.nama_varian") <small class="text-danger">{{ $message }}</small> @enderror

                                <label for="harga_{{ $i }}" class="form-label">Harga</label>
                                <input type="number" min="0" name="varian[{{ $i }}][harga]"
                                    id="harga_{{ $i }}" class="form-control"
                                    value="{{ old("varian.$i.harga", $varian->harga) }}" required>
                                @error("varian.$i.harga") <small class="text-danger">{{ $message }}</small> @enderror

                                <label for="stok_{{ $i }}" class="form-label">Stok</label>
                                <input type="number" min="0" name="varian[{{ $i }}][stok]"
                                    id="stok_{{ $i }}" class="form-control"
                                    value="{{ old("varian.$i.stok", $varian->stok) }}" required>
                                @error("varian.$i.stok") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <button type="button" class="btn btn-danger remove-row">Hapus</button>
                        </div>
                    @endforeach
                    {{-- (Tempat untuk baris varian dinamis) --}}
                </div>
            </div>

            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            {{-- PERBAIKAN 2: Tombol 'Kembali' diubah --}}
            <a href="{{ route('dashboard.toko.show') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
@endsection

{{--
  PERBAIKAN 4: JavaScript DIPERBARUI
  Memperbaiki bug Blade @foreach di dalam string JS
--}}
@push('scripts')
<script>
    // --- ( PERBAIKAN PADA JAVASCRIPT ) ---

    // 1. Ubah data Kategori dari PHP (Blade) ke JavaScript
    const allKategori = @json($kategori);
    let kategoriOptionsHtml = '<option value="">-- Pilih Kategori --</option>';
    allKategori.forEach(k => {
        kategoriOptionsHtml += `<option value="${k.id_kategori}">${k.nama_kategori}</option>`;
    });

    // 2. Tentukan index awal berdasarkan jumlah item yang sudah ada
    // Ini PENTING untuk form edit agar index tidak tumpang tindih
    let varianIndex = document.getElementById('varian-wrapper').children.length;
    let kategoriIndex = document.getElementById('kategori-wrapper').children.length;

    // 3. Event listener untuk menambah varian
    document.getElementById('add-varian-row').addEventListener('click', function () {
        const wrapper = document.getElementById('varian-wrapper');
        const newRow = document.createElement('div');
        newRow.classList.add('input-group', 'mb-2', 'varian-item');

        let i = varianIndex;

        // Untuk baris baru, input gambar 'required' (wajib)
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

        // Gunakan string HTML opsi kategori yang sudah kita buat
        newRow.innerHTML = `
            <div>
                <label for="kategori_${i}" class="form-label">Kategori</label>
                <select name="detail[${i}][kategori]" id="kategori_${i}" class="form-select" required>
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
