import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:io';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'product_form.dart';

//==={{{back-end}}}===
class ManageProductsPage extends StatefulWidget {
  const ManageProductsPage({super.key});

  @override
  State<ManageProductsPage> createState() => _ManageProductsPageState();
}

class _ManageProductsPageState extends State<ManageProductsPage> {
  List<dynamic> _products = [];
  bool _isLoading = true;
  String? _error;

  static String get _baseUrl {
    /*if (Platform.isAndroid) {
      return 'http://10.0.2.2:8000/api';
    }*/
    return 'http://127.0.0.1:8000/api';
  }

  static String get _storageUrl {
    /*if (Platform.isAndroid) {
      return 'http://10.0.2.2:8000/storage';
    }*/
    return 'http://127.0.0.1:8000/file/storage';
  }

  @override
  void initState() {
    super.initState();
    _fetchProducts();
  }

  Future<void> _fetchProducts() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('authToken');

      if (token == null) {
        setState(() {
          _error = 'Unauthorized';
          _isLoading = false;
        });
        return;
      }

      final response = await http.get(
        Uri.parse('$_baseUrl/manage/product'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          if (data is Map && data.containsKey('data')) {
            _products = data['data'];
          } else if (data is List) {
            _products = data;
          } else {
            _products = [];
          }
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = 'Failed to load products: ${response.statusCode}';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'Connection error: $e';
        _isLoading = false;
      });
    }
  }

  String _formatCurrency(dynamic amount) {
    final format = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);
    return format.format(amount);
  }

  Future<void> _deleteProduct(int id) async {
    bool confirm = await showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Hapus Produk'),
        content: const Text('Apakah Anda yakin ingin menghapus produk ini?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.pop(context, true), child: const Text('Hapus', style: TextStyle(color: Colors.red))),
        ],
      ),
    ) ?? false;

    if (!confirm) return;

    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('authToken');
      
      final response = await http.delete(
        Uri.parse('$_baseUrl/manage/product/$id'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Produk berhasil dihapus')));
        _fetchProducts();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Gagal menghapus produk')));
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    }
  }

  void _openForm({Map<String, dynamic>? product}) async {
    bool? result = await Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => ProductFormPage(product: product)),
    );

    if (result == true) {
      _fetchProducts();
    }
  }


  //==={{{Front-end}}}===
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Kelola Produk', style: TextStyle(color: Colors.black)),
        backgroundColor: Colors.white,
        elevation: 1,
        iconTheme: const IconThemeData(color: Colors.black),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: Colors.red)))
              : _products.isEmpty
                  ? const Center(child: Text('Belum ada produk.'))
                  : ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: _products.length,
                      itemBuilder: (context, index) {
                        final product = _products[index];
                        final variants = product['variant'] as List<dynamic>?;
                        final firstVariant = (variants != null && variants.isNotEmpty) ? variants[0] : null;
                        
                        final String? imagePath = firstVariant?['gambar_varian'];
                        final String imageUrl = imagePath != null 
                            ? (imagePath.startsWith('http') ? imagePath : '$_storageUrl/$imagePath')
                            : 'https://via.placeholder.com/150';
                            
                        final double price = double.tryParse(firstVariant?['harga']?.toString() ?? '0') ?? 0;
                        final int stock = int.tryParse(firstVariant?['stok']?.toString() ?? '0') ?? 0;

                        return Card(
                          margin: const EdgeInsets.only(bottom: 16),
                          elevation: 2,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          child: InkWell(
                            onTap: () => _openForm(product: product),
                            borderRadius: BorderRadius.circular(12),
                            child: Padding(
                              padding: const EdgeInsets.all(12),
                              child: Row(
                                children: [
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(8),
                                    child: Image.network(
                                      imageUrl,
                                      width: 80,
                                      height: 80,
                                      fit: BoxFit.cover,
                                      errorBuilder: (context, error, stackTrace) => Container(
                                        width: 80,
                                        height: 80,
                                        color: Colors.grey[200],
                                        child: const Icon(Icons.image_not_supported, color: Colors.grey),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 16),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          product['nama_produk'] ?? 'Product Name',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          _formatCurrency(price),
                                          style: const TextStyle(color: Colors.pink, fontWeight: FontWeight.bold),
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          'Stok: $stock | Terjual: ${product['terjual'] ?? 0}',
                                          style: const TextStyle(color: Colors.grey, fontSize: 12),
                                        ),
                                      ],
                                    ),
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.delete, color: Colors.red),
                                    onPressed: () => _deleteProduct(product['id_produk']),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        );
                      },
                    ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _openForm(),
        backgroundColor: Colors.pink,
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }
}
