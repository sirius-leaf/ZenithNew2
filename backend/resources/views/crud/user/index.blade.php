<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage User</title>
</head>
<body>
    <div style="padding: 20px;">
        <h1 style="font-size: 22px; font-weight: bold;">Daftar User</h1>

        {{-- Tabel User --}}
        <table border="1" cellpadding="8" cellspacing="0" style="margin-top: 20px; width: 100%; border-collapse: collapse;">
            <thead style="background: #f2f2f2;">
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>No Telepon</th>
                    <th>Alamat</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>Password</td>
                        <td>{{ $user->no_telpon ?? 'kosong' }}</td>
                        <td>{{ $user->alamat ?? 'kosong' }}</td>

                        {{-- Form ubah role --}}
                        <td>
                            <form action="{{ url('/dashboard/manage/user/' . $user->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <select name="role" onchange="this.form.submit()">
                                    <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                    <option value="penjual" {{ $user->role === 'penjual' ? 'selected' : '' }}>Penjual</option>
                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                            </form>
                        </td>

                        {{-- Tombol hapus user --}}
                        <td>
                            <form action="{{ url('/dashboard/manage/user/' . $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus akun ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="color: red; background: none; border: none; cursor: pointer;">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
