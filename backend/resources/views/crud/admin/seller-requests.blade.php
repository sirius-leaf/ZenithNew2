@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Seller Requests</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Request Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sellerRequests as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                    <td>
                        <form action="{{ route('dashboard.manage.admin.sellerRequests.approve', $user->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                        </form>
                    </td>
                </tr>
            @endforeach

            @if($sellerRequests->isEmpty())
                <tr>
                    <td colspan="4" class="text-center">No pending seller requests</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection