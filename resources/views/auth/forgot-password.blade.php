@php
    $setting = \App\Models\SystemSetting::first();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    @if (!empty($setting->favicon))
        <link rel="icon" type="image/x-icon" href="{{ asset($setting->favicon) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('backend/images/logo.png') }}">
    @endif
    <link rel="stylesheet" href="{{asset('frontend/assets/css/plugins/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/responsive.css')}}">
    <style>
        .success-message {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<section class="login-section">
    <div class="container">
        <div class="login-container">
            <div class="row g-xxl-5 g-4 flex-column-reverse flex-lg-row">
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
                            <h2>Forgot your password?</h2>
                        </div>

                        <!-- Password Reset Form -->
                        <form action="{{ route('password.email') }}" method="POST">
                            @csrf

                            <!-- Email Input Field -->
                            <div class="input-item">
                                <label for="email">Enter Your Email</label>
                                <input type="email" class="form-control shadow-none @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email') }}" placeholder="Enter Email">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status Message for Forgot Password -->
                            <div class="mb-4 text-sm">
                                {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                            </div>

                            <!-- Session Status -->
                            <x-auth-session-status class="mb-4 alert alert-success" :status="session('status')" />

                            <!-- Submit Button -->
                            <button type="submit" class="login-button">Send Password Reset Link</button>
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
