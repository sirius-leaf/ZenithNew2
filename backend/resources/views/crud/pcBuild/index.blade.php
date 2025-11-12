@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Daftar Pc Build</h2>
        <a href="{{ route('dashboard.manage.pcBuild.create') }}" class="btn btn-primary mb-3">+ Tambah Pc Build</a>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Build</th>
                    <th style="width: 185px">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($pcBuild as $p)
                    <tr>
                        <td>{{ $p->nama_build }}</td>
                        <td>
                            {{-- <a href="{{ route('dashboard.manage.produk.show', $p) }}"
                                class="btn btn-primary btn-sm">Show</a> --}}
                            <a href="{{ route('dashboard.manage.pcBuild.edit', $p) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('dashboard.manage.pcBuild.destroy', $p) }}" method="POST"
                                style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('Hapus template ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection