<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body style="margin:0; font-family: Arial, sans-serif; background-color: #e84797; position: relative; min-height: 100vh; overflow:hidden;">

    <!-- Wave Background -->
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" style="position:absolute; bottom:0; left:0; width:100%; transform: translateY(10px);">
        <path fill="#e7a0cc" fill-opacity="1" d="M0,288L80,245.3C160,203,320,117,480,96C640,75,800,117,960,112C1120,107,1280,53,1360,26.7L1440,0L1440,320L1360,320C1280,320,1120,320,960,320C800,320,640,320,480,320C320,320,160,320,80,320L0,320Z"></path>
    </svg>

    <!-- Container -->
    <div style="position:relative; z-index:10; display:flex; flex-direction:column; align-items:center; justify-content:center; height:100vh;">

        <!-- Card -->
        <div style="background-color:rgba(255,255,255,0.15); backdrop-filter:blur(12px); border-radius:20px; padding:40px; width:320px; box-shadow:0 4px 12px rgba(0,0,0,0.2); text-align:center;">

            <!-- Logo -->
            <div style="margin-bottom:20px;">
                <img src="/images/logo.png" alt="Logo" style="width:60px; height:60px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.3);">
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('login') }}" style="display:flex; flex-direction:column; gap:20px; text-align:left;">
                @csrf

                <!-- Email -->
                <div>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        placeholder="Email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        style="width:100%; border:none; border-bottom:1px solid white; background:transparent; color:white; padding:8px; outline:none;"
                    >
                    @error('email')
                        <div style="color: yellow; font-size:12px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Password"
                        required
                        style="width:100%; border:none; border-bottom:1px solid white; background:transparent; color:white; padding:8px; outline:none;"
                    >
                    @error('password')
                        <div style="color: yellow; font-size:12px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember me -->
                <div style="display:flex; align-items:center; justify-content:space-between; color:white; font-size:14px;">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                </div>

                <!-- Submit -->
                <div style="text-align:center; margin-top:10px;">
                    <button type="submit" style="background:white; color:#203f9a; border:none; border-radius:20px; padding:8px 20px; font-weight:bold; cursor:pointer;">
                        Log in
                    </button>
                </div>

                <!-- Register link -->
                <div style="text-align:center; color:white; font-size:14px; margin-top:10px;">
                    Don't have an account?
                    <a href="{{ route('register') }}" style="color:white; text-decoration:underline;">Sign up</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div style="position:absolute; bottom:0; left:0; right:0; background:#f2f2f2; color:#203f9a; height:40px; display:flex; align-items:center; justify-content:center; font-size:14px;">
        © 2025 Zenith. All rights reserved.
    </div>

</body>
</html>
