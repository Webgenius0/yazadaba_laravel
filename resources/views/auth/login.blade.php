@php
    $setting = \App\Models\SystemSetting::first();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    @if (!empty($setting->favicon))
        <link rel="icon" type="image/x-icon" href="{{ asset($setting->favicon) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('backend/images/logo.png') }}">
    @endif
    <link rel="stylesheet" href="{{asset('frontend/assets/css/plugins/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/responsive.css')}}">
</head>
<body>

<section class="login-section">
    <div class="container">
        <div class="login-container">
            <div
                class="row g-xxl-5 g-4 flex-column-reverse  flex-lg-row">
                <div class="col-lg-6 h-100">
                    <div class="login-image-wrap">
                        <div class="login-image">
                            <img src="{{asset('frontend/assets/images/login-img.png')}}" alt>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 h-100">
                    <div class="login-form-wrapper">
                        <div class="login-title">
                            <h2>Hello! Login now</h2>
                        </div>
                        <form action="{{ route('login') }}" method="POST">
                            @csrf <!-- CSRF Token for security -->
                            <div class="input-item">
                                <label for="email">Enter Email</label>
                                <input type="email"
                                       class="form-control shadow-none @error('email') is-invalid @enderror"
                                       name="email"
                                       placeholder="Enter Email"
                                       value="{{ old('email') }}">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-item">
                                <label for="password">Password</label>
                                <input type="password"
                                       class="form-control shadow-none @error('password') is-invalid @enderror"
                                       placeholder="Enter Password"
                                       name="password">
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="remember-main-wrapper">
                                <div class="remember-wrapper">
                                    <div class="form-check">
                                        <input class="form-check-input shadow-none"
                                               type="checkbox"
                                               name="remember"
                                               id="flexCheckChecked"
                                            {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="flexCheckChecked">
                                            Remember Me
                                        </label>
                                    </div>
                                </div>
                                <div class="forgot-password">
                                    <a href="{{ route('password.request') }}">Forget password</a>
                                </div>
                            </div>
                            <button type="submit" class="login-button">Log In</button>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="{{asset('frontend/assets/js/jquery-3.7.1.min.js')}}"></script>
<script src="{{asset('frontend/assets/js/plugins.js')}}"></script>
<script src="{{asset('frontend/assets/js/main.js')}}"></script>
</body>
</html>
