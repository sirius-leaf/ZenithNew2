# Pembagian Penjelasan Code Project Zenith Mobile

Berikut adalah overview pembagian tugas presentasi beserta link langsung ke file dan baris kode yang relevan.

## Overview Pembagian Tugas

| Kode    | Penanggung Jawab | Fokus Utama                                  | File Utama                                         |
| :------ | :--------------- | :------------------------------------------- | :------------------------------------------------- |
| **Sa**  | **Sa**           | App Init, Main Layout, Login Logic           | [`lib/main.dart`](main.dart)                       |
| **Ha**  | **Ha**           | Dashboard UI, Banner, Static Widgets         | [`lib/dashboard.dart`](dashboard.dart)             |
| **Ra**  | **Ra**           | Fetch Data Produk, Product Card, Formatting  | [`lib/dashboard.dart`](dashboard.dart)             |
| **Man** | **Man**          | List Produk Admin, Hapus Produk, Auth Header | [`lib/manage_products.dart`](manage_products.dart) |
| **Gi**  | **Gi**           | Form Produk Basic, Logic Kategori Dinamis    | [`lib/product_form.dart`](product_form.dart)       |
| **Za**  | **Za**           | Form Advanced, Varian, Upload Gambar, Save   | [`lib/product_form.dart`](product_form.dart)       |

---

## 1. Saji: App Initialization & Login

**File:** [`lib/main.dart`](main.dart)

### Detail Kode & Link:

1.  **`main()` & `MyApp`**

    - Lokasi: [Baris 9-28](main.dart#L9-L28)
    - Fungsi: Titik start aplikasi. Mengatur tema (`ThemeData`) dan routing awal.
    - Kode:
      ```dart
      void main() {
        runApp(const MyApp());
      }
      ```

2.  **`_login()` (Logic Autentikasi)**

    - Lokasi: [Baris 53-121](main.dart#L53-L121)
    - Fungsi: Mengirim data email/password ke API dan menyimpan token.
    - Kode:
      ```dart
      final response = await http.post(
        Uri.parse('$_baseUrl/login'),
        // ...
      );
      // Simpan token jika sukses
      await prefs.setString('authToken', token);
      ```

3.  UI Login (Stack & Layout)
    - Lokasi: [Baris 124-320](main.dart#L124-L320)
    - Fungsi: Menggunakan `Stack` untuk background SVG dlm posisi `Positioned.fill`.
    - Kode:
      ```dart
      return Stack(
        children: [
          // ...
        ],
      );
      ```

---

## 2. Hafiz: Dashboard UI & Structure

    File: [`lib/dashboard.dart`](dashboard.dart)

### Detail Kode & Link:

1.  **Struktur Halaman (`DashboardPage`)**

    - Lokasi: [Baris 87-117](dashboard.dart#L87-L117)
    - Fungsi: `Scaffold` dengan `AppBar` custom berisi ikon navigasi.
    - Kode:
      ```dart
      actions: [
        IconButton(
          icon: const Icon(Icons.store, color: Colors.grey),
          onPressed: () async { ... }, // Navigasi ke ManageProducts
        ),
        // ...
      ]
      ```

2.  **Banner Promosi**

    - Lokasi: [Baris 127-173](dashboard.dart#L127-L173)
    - Fungsi: Container dengan dekorasi gambar dan gradient untuk visual promosi.
    - Kode:
      ```dart
      return Container(
        // ...
      );
      ```

3.  **Widget "Tag Populer" (`_buildTagItem`)**
    - Lokasi: [Baris 227-251](dashboard.dart#L227-L251)
    - Fungsi: Widget reusable untuk menampilkan ikon kategori.
    - Kode:
      ```dart
      Widget _buildTagItem(String label, IconData icon) {
        return Container( ... ); // Kotak putih dengan shadow
      }
      ```

---

## 3. Radit: Dashboard Data Fetching

File: [`lib/dashboard.dart`](dashboard.dart)

### Detail Kode & Link:

1.  **`_fetchProducts()` (Logic Fetch Data)**

    - Lokasi: [Baris 42-71](dashboard.dart#L42-L71)
    - Fungsi: Request GET ke API dan parsing JSON ke List.
    - Kode:
      ```dart
      final response = await http.get(Uri.parse('$_baseUrl/products'));
      if (response.statusCode == 200) {
        // Logic parsing JSON data['data'] atau List langsung
        setState(() { _products = data; });
      }
      ```

2.  `_buildProductCard()` (Tampilan Produk)

    - Lokasi: [Baris 253-371](dashboard.dart#L253-L371)
    - Fungsi: Menangani URL gambar (lokal vs remote) dan layout kartu produk.
    - Kode:
      ```dart
      final String imageUrl = imagePath != null
          ? (imagePath.startsWith('http') ? imagePath : '$_storageUrl/$imagePath')
          : '...';
      ```

3.  **`_formatCurrency()`**
    - Lokasi: [Baris 73-76](dashboard.dart#L73-L76)
    - Fungsi: Helper untuk format Rupiah.

---

## 4. Manda: Manage Products List

File: [`lib/manage_products.dart`](manage_products.dart)

### Detail Kode & Link:

1.  Request dengan Auth Header

    - Lokasi: [Baris 41-86 (`_fetchProducts`)](manage_products.dart#L41-L86)
    - Fungsi: Mengambil token dari storage dan menyisipkan ke header request.
    - Kode:
      ```dart
      headers: {
        'Authorization': 'Bearer $token', // Penting!
        'Accept': 'application/json',
      },
      ```

2.  `_deleteProduct()` (Hapus Produk)
    - Lokasi: [Baris 93-129](manage_products.dart#L93-L129)
    - Fungsi: Menampilkan dialog konfirmasi dan mengirim request DELETE.
    - Kode:
      ```dart
      bool confirm = await showDialog( ... ); // Tanya user dulu
      if (!confirm) return;
      await http.delete( ... ); // Hapus ke server
      ```

---

## 5. Abdian: Product Form "Basic" & Categories

File: [`lib/product_form.dart`](product_form.dart)

### Detail Kode & Link:

1.  Inisialisasi Mode Form (Create/Edit)

    - Lokasi: [Baris 40-48 (`initState`)](product_form.dart#L40-L48)
    - Fungsi: Cek apakah `widget.product` null atau ada isinya.

2.  `_fetchCategories()`

    - Lokasi: [Baris 97-109](product_form.dart#L97-L109)
    - Fungsi: Ambil data kategori untuk dropdown.

3.  Logic Kategori Dinamis (`_selectedCategories`)
    - Lokasi (Deklarasi): [Baris 29](product_form.dart#L29)
    - Lokasi (UI): [Baris 265-316](product_form.dart#L265-L316)
    - Fungsi: Menambah/menghapus baris dropdown kategori secara dinamis.
    - Kode:
      ```dart
      // Tombol Tambah
      onPressed: () {
        setState(() { _selectedCategories.add({'id_kategori': null}); });
      },
      ```

---

## 6. Za: Product Form "Advanced" (Variants & Save)

File: [`lib/product_form.dart`](product_form.dart)

### Detail Kode & Link:

1.  Logic Varian Dinamis (`_variants`)

    - Lokasi (Deklarasi): [Baris 30](product_form.dart#L30)
    - Lokasi (UI): [Baris 319-434](product_form.dart#L319-L434)
    - Fungsi: Mengelola list varian (nama, harga, stok, gambar).

2.  `_pickImage()` (Upload Gambar)

    - Lokasi: [Baris 111-124](product_form.dart#L111-L124)
    - Fungsi: Membuka galeri dan menyimpan file gambar ke state.
    - Kode:
      ```dart
      final pickedFile = await picker.pickImage(source: ImageSource.gallery);
      // ... simpan ke _variants[index]
      ```

3.  `_saveProduct()` (Simpan Data)
    - Lokasi: [Baris 126-218](product_form.dart#L126-L218)
    - Fungsi: Handle Multipart Request untuk kirim data + file sekaligus.
    - Kode:
      ```dart
      var request = http.MultipartRequest('POST', uri);
      // Loop varian dan add fields
      request.fields['varian[$i][harga]'] = v['harga'];
      // Add file gambar
      request.files.add( await http.MultipartFile.fromBytes(...) );
      ```
