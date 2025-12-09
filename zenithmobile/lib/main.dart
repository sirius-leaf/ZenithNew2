import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:io';
import 'package:shared_preferences/shared_preferences.dart';
import 'dashboard.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Zenith Mobile',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.deepPurple),
        useMaterial3: true,
        fontFamily: 'Ubuntu',
      ),
      home: const LoginPage(),
    );
  }
}

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  String? _errorMessage;



  // Use 10.0.2.2 for Android emulator, localhost for others
  static String get _baseUrl {
    if (Platform.isAndroid) {
      return 'http://10.0.2.2:8000/api';
    }
    return 'http://127.0.0.1:8000/api';
  }

  Future<void> _login() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/login'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'email': _emailController.text,
          'password': _passwordController.text,
          'recaptcha': 'mobile_dev_bypass',
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200) {
        final token = data['token'];
        final user = data['user'];
        final role = user['role'];

        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('authToken', token);
        await prefs.setString('userRole', role);

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Login Berhasil!')),
          );
          // Navigate to dashboard or home screen here
          // For now, just show a success dialog


// ... imports ...

// ... inside _login method ...

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Login Berhasil!')),
          );
          
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (context) => const DashboardPage()),
          );
        }
        }
      } else {
        setState(() {
          _errorMessage = data['message'] ?? 'Login failed';
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Terjadi kesalahan koneksi. Coba lagi nanti.';
      });
      debugPrint('Login error: $e');
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          // Background Color
          Container(
            color: const Color(0xFFEC4899), // Pink-500
          ),
          
          // Wave Background
          Positioned.fill(
            child: SvgPicture.string(
              '''
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 526" preserveAspectRatio="none">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M0 455.714 L60 435 C120 410 240 365 360 340 C480 320 600 315 720 308 C840 300 960 280 1080 250 C1200 220 1320 175 1380 150 L1440 130 V646 H0 Z" fill="#E7A0CC"/>
              </svg>
              ''',
              fit: BoxFit.cover,
              alignment: Alignment.topCenter,
            ),
          ),

          // Back Button
          Positioned(
            top: 40,
            left: 16,
            child: TextButton.icon(
              onPressed: () {
                // Navigator.pop(context); // No navigation stack yet
              },
              icon: const Icon(Icons.arrow_back, color: Colors.white),
              label: const Text(
                'Kembali',
                style: TextStyle(color: Colors.white, fontSize: 14),
              ),
              style: TextButton.styleFrom(
                padding: EdgeInsets.zero,
                minimumSize: Size.zero,
                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
            ),
          ),

          // Main Content
          Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Container(
                constraints: const BoxConstraints(maxWidth: 320),
                padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 28),
                decoration: BoxDecoration(
                  color: const Color(0xFF1E3A8A).withOpacity(0.2), // Blue-900 with opacity
                  borderRadius: BorderRadius.circular(5),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.1),
                      blurRadius: 20,
                      offset: const Offset(0, 10),
                    ),
                  ],
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // Logo
                    Image.asset(
                      'assets/zenith.png',
                      width: 64,
                      height: 64,
                    ),
                    const SizedBox(height: 40),

                    // Email Field
                    _buildTextField(
                      controller: _emailController,
                      hintText: 'Username / Email',
                      obscureText: false,
                    ),
                    const SizedBox(height: 24),

                    // Password Field
                    _buildTextField(
                      controller: _passwordController,
                      hintText: 'Password',
                      obscureText: true,
                    ),
                    const SizedBox(height: 24),

                    // Error Message
                    if (_errorMessage != null)
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(10),
                        margin: const EdgeInsets.only(bottom: 20),
                        decoration: BoxDecoration(
                          color: Colors.red[100],
                          borderRadius: BorderRadius.circular(5),
                        ),
                        child: Text(
                          _errorMessage!,
                          style: TextStyle(color: Colors.red[800], fontSize: 12),
                          textAlign: TextAlign.center,
                        ),
                      ),

                    // Login Button
                    SizedBox(
                      width: double.infinity,
                      height: 40,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _login,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: const Color(0xFF1E3A8A), // Blue-900
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(20),
                          ),
                          elevation: 0,
                        ),
                        child: _isLoading
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF1E3A8A)),
                                ),
                              )
                            : const Text(
                                'Login',
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                ),
                              ),
                      ),
                    ),

                    const SizedBox(height: 16),

                    // Register Link
                    Column(
                      children: [
                        const Text(
                          'Belom punya akun?',
                          style: TextStyle(
                            color: Colors.white70,
                            fontSize: 12,
                          ),
                        ),
                        const SizedBox(height: 4),
                        GestureDetector(
                          onTap: () {
                            // Navigate to register
                          },
                          child: const Text(
                            'Daftar sekarang',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              decoration: TextDecoration.underline,
                              decorationColor: Colors.white,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),

          // Footer
          Align(
            alignment: Alignment.bottomCenter,
            child: Container(
              width: double.infinity,
              height: 56,
              color: Colors.white,
              alignment: Alignment.centerLeft,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: const Text(
                '@ 2025 Zenith. All rights reserved.',
                style: TextStyle(
                  color: Color(0xFF1E3A8A), // Blue-900
                  fontSize: 12,
                  fontWeight: FontWeight.normal,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String hintText,
    required bool obscureText,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        TextField(
          controller: controller,
          obscureText: obscureText,
          style: const TextStyle(color: Colors.white, fontSize: 14),
          decoration: InputDecoration(
            hintText: hintText,
            hintStyle: const TextStyle(color: Colors.white70, fontSize: 14),
            border: InputBorder.none,
            isDense: true,
            contentPadding: const EdgeInsets.only(bottom: 8),
          ),
        ),
        Container(
          height: 1,
          color: Colors.white,
        ),
      ],
    );
  }
}
