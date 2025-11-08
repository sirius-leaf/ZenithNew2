<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>

<body>
    <div style="padding: 20px;">

        {{-- Header Dashboard --}}
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Dashboard</h2>

            {{-- Tombol Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    style="background-color: #e74c3c; color: white; border: none; padding: 8px 14px; border-radius: 5px; cursor: pointer;">
                    Logout
                </button>
            </form>
        </div>

        {{-- Link navigasi --}}
        <div style="margin: 20px 0;">
            <a href="{{ route('dashboard.manage.produk.index') }}" style="margin-right: 10px;">Manage Produk</a> |
            <a href="/dashboard/manage/user" style="margin-left: 10px;">Manage User</a>

            {{-- Tombol menuju Seller Requests (hanya admin) --}}
            @if (Auth::user()->role === 'admin')
                |
                <a href="/dashboard/manage/admin/seller-requests"
                    style="margin-left: 10px; background-color: #9b59b6; color: white; padding: 6px 10px; border-radius: 5px; text-decoration: none;">
                    Seller Requests
                </a>
            @endif
        </div>

        {{-- Informasi pengguna --}}
        <div>
            <p>Halo, {{ Auth::user()->name }}!</p>

            @if (Auth::user()->role === 'user')
                <form method="POST" action="/dashboard/manage/become-seller">
                    @csrf
                    <button type="submit"
                        style="background-color: #3498db; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
                        Daftar Menjadi Penjual
                    </button>
                </form>
            @elseif (Auth::user()->role === 'penjual_pending')
                <p>Permintaan menjadi penjual sedang menunggu konfirmasi admin.</p>
            @elseif (Auth::user()->role === 'penjual')
                <p>Anda sudah menjadi penjual.</p>
                <a href="/dashboard/manage/toko"
                    style="background-color: #2ecc71; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none;">
                    Manage Toko
                </a>
            @endif
        </div>

        {{-- Placeholder tampilan konten --}}
        <hr style="margin: 30px 0;">

        <div>
            <h3>Konten Utama</h3>
            <div style="display: flex; gap: 10px;">
                <div
                    style="border: 1px solid #aaa; width: 200px; height: 100px; display:flex;align-items:center;justify-content:center;">
                    Placeholder
                </div>
                <div
                    style="border: 1px solid #aaa; width: 200px; height: 100px; display:flex;align-items:center;justify-content:center;">
                    Placeholder
                </div>
                <div
                    style="border: 1px solid #aaa; width: 200px; height: 100px; display:flex;align-items:center;justify-content:center;">
                    Placeholder
                </div>
            </div>

            <div
                style="margin-top: 30px; border: 1px solid #aaa; height: 200px; display:flex;align-items:center;justify-content:center;">
                Placeholder (Area Besar)
            </div>
        </div>
    </div>
</body>

</html>