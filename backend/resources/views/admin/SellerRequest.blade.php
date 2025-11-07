@extends('layout')

@section('content')
<div style="padding: 2rem;">
    <h1 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">Permintaan Menjadi Penjual</h1>

    @if (session('success'))
        <div style="background: #22c55e; color: white; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
            {{ session('success') }}
        </div>
    @endif

    @if ($requests->isEmpty())
        <p>Tidak ada permintaan menjadi penjual.</p>
    @else
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #ccc;">
            <thead style="background: #f3f4f6;">
                <tr>
                    <th style="border: 1px solid #ccc; padding: 8px;">Nama</th>
                    <th style="border: 1px solid #ccc; padding: 8px;">Email</th>
                    <th style="border: 1px solid #ccc; padding: 8px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requests as $user)
                    <tr>
                        <td style="border: 1px solid #ccc; padding: 8px;">{{ $user->name }}</td>
                        <td style="border: 1px solid #ccc; padding: 8px;">{{ $user->email }}</td>
                        <td style="border: 1px solid #ccc; padding: 8px;">
                            <form action="{{ url('/dashboard/manage/admin/seller-requests/' . $user->id . '/approve') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" style="background-color: #22c55e; color: white; padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer;">
                                    Approve
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
