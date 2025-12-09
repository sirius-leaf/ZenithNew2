@extends('layout')

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(to bottom right, #111827, #1f2937, #000); padding: 2rem;">
    <div style="width: 100%; max-width: 500px; background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); border-radius: 16px; box-shadow: 0 10px 20px rgba(0,0,0,0.4); padding: 2rem;">
        <h1 style="font-size: 1.75rem; font-weight: 600; color: white; text-align: center; margin-bottom: 1.5rem;">
            Edit User
        </h1>

        @if (session('success'))
            <div style="background: #22c55e; color: white; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background: #ef4444; color: white; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('dashboard.manage.user.update', $user->id) }}" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            @csrf
            @method('PUT')

            {{-- Nama --}}
            <div>
                <label style="display:block; color:#d1d5db; margin-bottom:4px;">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.2); background:rgba(255,255,255,0.1); color:white;">
            </div>

            {{-- Password --}}
            <div>
                <label style="display:block; color:#d1d5db; margin-bottom:4px;">Password (opsional)</label>
                <input type="password" name="password" placeholder="Isi password baru..." class="input" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.2); background:rgba(255,255,255,0.1); color:white;">
            </div>

            {{-- No Telpon --}}
            <div>
                <label style="display:block; color:#d1d5db; margin-bottom:4px;">No Telpon</label>
                <input type="text" name="no_telpon" value="{{ old('no_telpon', $user->no_telpon) }}" class="input" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.2); background:rgba(255,255,255,0.1); color:white;">
            </div>

            {{-- Alamat --}}
            <div>
                <label style="display:block; color:#d1d5db; margin-bottom:4px;">Alamat</label>
                <textarea name="alamat" rows="3" class="input" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.2); background:rgba(255,255,255,0.1); color:white;">{{ old('alamat', $user->alamat) }}</textarea>
            </div>

            {{-- Role --}}
            <div>
                <label style="display:block; color:#d1d5db; margin-bottom:4px;">Role</label>
                <select name="role" style="width:100%; padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.2); background:rgba(255,255,255,0.1); color:white;">
                    <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User</option>
                    <option value="penjual" {{ $user->role == 'penjual' ? 'selected' : '' }}>Penjual</option>
                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            {{-- Tombol --}}
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:1rem;">
                <a href="{{ route('dashboard.manage.user.index') }}" style="color:#9ca3af; text-decoration:none;">← Kembali</a>
                <button type="submit" style="background:#2563eb; color:white; padding:10px 20px; border:none; border-radius:8px; cursor:pointer; font-weight:600;">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
