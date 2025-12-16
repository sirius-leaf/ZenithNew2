import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:io';
import 'package:image_picker/image_picker.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ProductFormPage extends StatefulWidget {
  final Map<String, dynamic>? product; 

  const ProductFormPage({super.key, this.product});

  @override
  State<ProductFormPage> createState() => _ProductFormPageState();
}

class _ProductFormPageState extends State<ProductFormPage> {
  final _formKey = GlobalKey<FormState>();
  bool _isLoading = false;
  List<dynamic> _categories = [];
  
  // Form Fields
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _brandController = TextEditingController();
  final TextEditingController _descController = TextEditingController();
  
  List<Map<String, dynamic>> _selectedCategories = []; // List of {id_kategori: ...}
  List<Map<String, dynamic>> _variants = []; // List of {id_varian: ...}

  static String get _baseUrl {
    return 'http://127.0.0.1:8000/api';


  }

  @override
  void initState() {
    super.initState();
    _fetchCategories();
    if (widget.product != null) {
      _initEditMode();
    } else {
      _initCreateMode();
    }
  }

  void _initCreateMode() {
    _variants.add({ 
      'nama_varian': 'Standard',
      'harga': '0',
      'stok': '0',
      'gambar_varian': null, // File
      'gambar_preview': null, // String URL or File path
    });
    _selectedCategories.add({'id_kategori': null});
  }

  void _initEditMode() {
    final p = widget.product!; // p = produk
    _nameController.text = p['nama_produk'] ?? '';
    _brandController.text = p['merek'] ?? '';
    _descController.text = p['deskripsi'] ?? '';

    // Categories
    if (p['category_detail'] != null) { 
      for (var cat in p['category_detail']) {
        _selectedCategories.add({'id_kategori': cat['id_kategori']});
      }
    }
    if (_selectedCategories.isEmpty) { 
      _selectedCategories.add({'id_kategori': null}); 
    }

    // Variants
    if (p['variant'] != null) { 
      for (var v in p['variant']) {
        _variants.add({
          'id_varian': v['id_varian'],
          'nama_varian': v['nama_varian'],
          'harga': v['harga'].toString(),
          'stok': v['stok'].toString(),
          'gambar_varian': null,
          'gambar_preview': v['gambar_varian'] != null 
              ? '$_baseUrl/../file/storage/${v['gambar_varian']}'
              : null, 
        });
      }
    }
    if (_variants.isEmpty) {
      _initCreateMode();
    }
  }

  Future<void> _fetchCategories() async {
    try {
      final response = await http.get(Uri.parse('$_baseUrl/categories'));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          _categories = data['data'];
        });
      }
    } catch (e) {
      print('Error fetching categories: $e');
    }
  }

  Future<void> _pickImage(int index) async {
    final picker = ImagePicker();
    final pickedFile = await picker.pickImage(source: ImageSource.gallery);

    if (pickedFile != null) {
      Uint8List fileBytes = await pickedFile.readAsBytes();

      setState(() {
        _variants[index]['gambar_varian'] = File(pickedFile.path);
        _variants[index]['gambar_preview'] = pickedFile.path; // Local path for preview
        _variants[index]['gambar_bytes'] = fileBytes;
      });
    }
  }

  Future<void> _saveProduct() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('authToken');
      
      var uri = Uri.parse('$_baseUrl/manage/product');
      if (widget.product != null) {
        uri = Uri.parse('$_baseUrl/manage/product/${widget.product!['id_produk']}');
      }

      var request = http.MultipartRequest('POST', uri);
      request.headers['Authorization'] = 'Bearer $token';
      request.headers['Accept'] = 'application/json';

      if (widget.product != null) {
        request.fields['_method'] = 'PUT';
      }

      request.fields['nama_produk'] = _nameController.text;
      request.fields['merek'] = _brandController.text;
      request.fields['deskripsi'] = _descController.text;

      // Categories
      for (int i = 0; i < _selectedCategories.length; i++) {
        if (_selectedCategories[i]['id_kategori'] != null) {
          request.fields['kategori[$i]'] = _selectedCategories[i]['id_kategori'].toString();
        }
      }

      // Variants
      for (int i = 0; i < _variants.length; i++) {
        final v = _variants[i];
        if (v['id_varian'] != null) {
          request.fields['varian[$i][id_varian]'] = v['id_varian'].toString();
        }
        request.fields['varian[$i][nama_varian]'] = v['nama_varian'];
        request.fields['varian[$i][harga]'] = v['harga'];
        request.fields['varian[$i][stok]'] = v['stok'];

        if (v['gambar_varian'] is File) {
          request.files.add(
            await http.MultipartFile.fromBytes(
              'varian[$i][gambar_varian]',
              v['gambar_bytes'],
              filename: v['gambar_filename'] ?? "gambar.png",
              contentType: http.MediaType('image', 'jpeg'), // optional
            ),
          );
        }
      }

      final streamResponse = await request.send();
      final response = await http.Response.fromStream(streamResponse);

      if (response.statusCode == 200 || response.statusCode == 201) {
        if (mounted) {
          Navigator.pop(context, true); // Return true to refresh
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Produk berhasil disimpan')),
          );
        }
      } else {
        print(response.body);
        String message = 'Gagal menyimpan: ${response.statusCode}';
        try {
          final errorData = jsonDecode(response.body);
          if (errorData['message'] != null) {
            message = errorData['message'];
          }
        } catch (e) {
          // ignore
        }
        
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(message)),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    } finally {
      setState(() => _isLoading = false);
    }
  }


  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.product == null ? 'Tambah Produk' : 'Edit Produk', style: const TextStyle(color: Colors.black)),
        backgroundColor: Colors.white,
        elevation: 1,
        iconTheme: const IconThemeData(color: Colors.black),
        actions: [
          IconButton(
            icon: const Icon(Icons.check, color: Colors.pink),
            onPressed: _isLoading ? null : _saveProduct,
          )
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Form(
              key: _formKey,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Basic Info
                  const Text('Informasi Produk', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _nameController,
                    decoration: const InputDecoration(labelText: 'Nama Produk', border: OutlineInputBorder()),
                    validator: (v) => v!.isEmpty ? 'Wajib diisi' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _brandController,
                    decoration: const InputDecoration(labelText: 'Merek', border: OutlineInputBorder()),
                    validator: (v) => v!.isEmpty ? 'Wajib diisi' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _descController,
                    decoration: const InputDecoration(labelText: 'Deskripsi', border: OutlineInputBorder()),
                    maxLines: 3,
                  ),
                  const SizedBox(height: 20),

                  // Categories
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Kategori', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                      TextButton(
                        onPressed: () {
                          setState(() {
                            _selectedCategories.add({'id_kategori': null});
                          });
                        },
                        child: const Text('+ Tambah'),
                      ),
                    ],
                  ),
                  ..._selectedCategories.asMap().entries.map((entry) {
                    int idx = entry.key;
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 8.0),
                      child: Row(
                        children: [
                          Expanded(
                            child: DropdownButtonFormField<int>(
                              value: _selectedCategories[idx]['id_kategori'],
                              items: _categories.map<DropdownMenuItem<int>>((c) {
                                return DropdownMenuItem<int>(
                                  value: c['id_kategori'],
                                  child: Text(c['nama_kategori']),
                                );
                              }).toList(),
                              onChanged: (val) {
                                setState(() {
                                  _selectedCategories[idx]['id_kategori'] = val;
                                });
                              },
                              decoration: const InputDecoration(border: OutlineInputBorder()),
                              hint: const Text('Pilih Kategori'),
                            ),
                          ),
                          if (_selectedCategories.length > 1)
                            IconButton(
                              icon: const Icon(Icons.delete, color: Colors.red),
                              onPressed: () {
                                setState(() {
                                  _selectedCategories.removeAt(idx);
                                });
                              },
                            ),
                        ],
                      ),
                    );
                  }).toList(),
                  const SizedBox(height: 20),

                  // Variants
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Varian Produk', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                      TextButton(
                        onPressed: () {
                          setState(() {
                            _variants.add({
                              'nama_varian': '',
                              'harga': '0',
                              'stok': '0',
                              'gambar_varian': null,
                              'gambar_preview': null,
                            });
                          });
                        },
                        child: const Text('+ Tambah Varian'),
                      ),
                    ],
                  ),
                  ..._variants.asMap().entries.map((entry) {
                    int idx = entry.key;
                    var v = entry.value;
                    return Card(
                      margin: const EdgeInsets.only(bottom: 16),
                      child: Padding(
                        padding: const EdgeInsets.all(12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text('Varian #${idx + 1}', style: const TextStyle(fontWeight: FontWeight.bold)),
                                if (_variants.length > 1)
                                  IconButton(
                                    icon: const Icon(Icons.delete, color: Colors.red),
                                    onPressed: () {
                                      setState(() {
                                        _variants.removeAt(idx);
                                      });
                                    },
                                  ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Image Picker
                                GestureDetector(
                                  onTap: () => _pickImage(idx),
                                  child: Container(
                                    width: 80,
                                    height: 80,
                                    decoration: BoxDecoration(
                                      color: Colors.grey[200],
                                      border: Border.all(color: Colors.grey),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: v['gambar_preview'] != null
                                        ? ClipRRect(
                                            borderRadius: BorderRadius.circular(8),
                                            child: v['gambar_varian'] is File
                                                ? Image.memory(
                                                    v['gambar_bytes'],
                                                    fit: BoxFit.cover,
                                                  )
                                                : Image.network(v['gambar_preview'], fit: BoxFit.cover,
                                                    errorBuilder: (c,e,s) => const Icon(Icons.error)),
                                          )
                                        : const Icon(Icons.add_a_photo, color: Colors.grey),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    children: [
                                      TextFormField(
                                        initialValue: v['nama_varian'],
                                        decoration: const InputDecoration(labelText: 'Nama Varian', isDense: true),
                                        onChanged: (val) => v['nama_varian'] = val,
                                        validator: (val) => val!.isEmpty ? 'Required' : null,
                                      ),
                                      const SizedBox(height: 8),
                                      Row(
                                        children: [
                                          Expanded(
                                            child: TextFormField(
                                              initialValue: v['harga'],
                                              decoration: const InputDecoration(labelText: 'Harga', isDense: true),
                                              keyboardType: TextInputType.number,
                                              onChanged: (val) => v['harga'] = val,
                                            ),
                                          ),
                                          const SizedBox(width: 8),
                                          Expanded(
                                            child: TextFormField(
                                              initialValue: v['stok'],
                                              decoration: const InputDecoration(labelText: 'Stok', isDense: true),
                                              keyboardType: TextInputType.number,
                                              onChanged: (val) => v['stok'] = val,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    );
                  }).toList(),
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton(
                      onPressed: _isLoading ? null : _saveProduct,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.pink,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      child: _isLoading 
                          ? const CircularProgressIndicator(color: Colors.white)
                          : const Text('Simpan Produk', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    ),
                  ),
                  const SizedBox(height: 24),
                ],
              ),
            ),
    );
  }
}
