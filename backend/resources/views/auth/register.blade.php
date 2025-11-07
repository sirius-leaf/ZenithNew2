<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>
    <div>
        <h2>Create an account</h2>
        <p>Enter your details below to create your account</p>

        {{-- Pesan error --}}
        @if ($errors->any())
            <div>
                <strong>Whoops! Something went wrong.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Register --}}
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <label for="name">Name</label><br>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Full name">
                @error('name')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <br>

            <div>
                <label for="email">Email address</label><br>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="email@example.com">
                @error('email')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <br>

            <div>
                <label for="password">Password</label><br>
                <input id="password" type="password" name="password" required placeholder="Password">
                @error('password')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <br>

            <div>
                <label for="password_confirmation">Confirm password</label><br>
                <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Confirm password">
                @error('password_confirmation')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <br>

            <div>
                <button type="submit">Create account</button>
            </div>
            <br>

            <div>
                Already have an account?
                {{-- <a href="{{ route('login') }}">Log in</a> --}}
            </div>
        </form>
    </div>
</body>
</html>
