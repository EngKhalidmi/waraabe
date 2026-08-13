<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>Saacid - Reset Password</title>

<link rel="shortcut icon" href="{{asset('/theme/assets/img/favicon1.png')}}">

<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="{{asset('/theme/assets/plugins/bootstrap/css/bootstrap.min.css')}}">

<link rel="stylesheet" href="{{asset('/theme/assets/plugins/feather/feather.css')}}">

<link rel="stylesheet" href="{{asset('/theme/assets/plugins/icons/flags/flags.css')}}">

<link rel="stylesheet" href="{{asset('/theme/assets/plugins/fontawesome/css/fontawesome.min.css')}}">
<link rel="stylesheet" href="{{asset('/theme/assets/plugins/fontawesome/css/all.min.css')}}">

<link rel="stylesheet" href="{{asset('/theme/assets/css/style.css')}}">


@include('partials.icons')
</head>
<style>
        @font-face {
            font-family: 'SF UI Text';
            src: url('{{ asset('theme/assets/fonts/SFUI/SFUIText-Regular.ttf') }}') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        /* Import SF UI Text Bold */
        @font-face {
            font-family: 'SF UI Text';
            src: url('{{ asset('theme/assets/fonts/SFUI/SFUIText-Bold.ttf') }}') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        * {
            font-family: 'SF UI Text', Arial, sans-serif;
            /* font-family: "Poppins", sans-serif; */
            font-weight: 400;
            font-style: normal;
        }

        /* Container for the toast */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }

        /* Common styling for both success and error toast messages */
        .toast-message {
            display: flex;
            align-items: center;
            background-color: white;
            border-radius: 12px;
            padding: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            border: 0.25px solid;
            max-width: 400px;
            font-family: Arial, sans-serif;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s ease-in-out; /* Fade-in/out transition */
        }

        /* Success message design */
        .toast-message.success {
            border-color: #2d8b41; /* Green border for success */
            background-color: #fff; /* Light green background */
            color: #2d8b41; /* Dark green text */
        }

        /* Error message design */
        .toast-message.error {
            border-color: #c0392b; /* Red border for error */
            background-color: #fff; /* Light red background */
            color: #c0392b; /* Dark red text */
        }

        /* Icon styling */
        .toast-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 8px;
            margin-right: 10px;
        }

        .toast-message.success .toast-icon {
            background-color: #eefced; /* Light green circle for success */
        }

        .toast-message.error .toast-icon {
            background-color: #fceded; /* Light red circle for error */
        }

        .icon-checkmark, .icon-error {
            font-size: 26px !important; /* Make the icon bigger */
        }

        .icon-checkmark {
            color: #31C75F; /* Green checkmark */
        }

        .icon-error {
            color: #fa0707; /* Red exclamation */
        }

        /* Content inside the toast */
        .toast-content {
            display: flex;
            flex-direction: column;
        }

        .toast-content strong {
            font-weight: bold;
            margin-bottom: 2px;
            margin-right: 5px;
            color: black;
        }

        .toast-content p {
            margin: 0;
            color: black;
        }

        /* Fade in and out animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                visibility: visible;
            }
            to {
                opacity: 1;
                visibility: visible;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                visibility: visible;
            }
            to {
                opacity: 0;
                visibility: hidden;
            }
        }
</style>
<body>

@if (session('status'))
    <div class="toast-container">
        <div class="toast-message success">
            <div class="toast-icon">
                <i data-lucide="circle-check" class="icon-checkmark"></i> <!-- Success checkmark icon -->
            </div>
            <div class="toast-content">
                <strong>Success!</strong>
                <p>{{ session('status') }}</p>
            </div>
        </div>
    </div>
@endif

@if ($errors->any() || session('error'))
    <div class="toast-container">
        <div class="toast-message error">
            <div class="toast-icon">
                <i data-lucide="circle-alert" class="icon-error"></i>
            </div>
            <div class="toast-content">
                <strong>Error!</strong>
                <p>
                    @if (session('error'))
                        {{ session('error') }}
                    @else
                        @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    @endif
                </p>
            </div>
        </div>
    </div>
@endif


<div class="main-wrapper login-body">
<div class="login-wrapper">
<div class="container">
<div class="loginbox">
<div class="login-left">
<img class="img-fluid" src="{{asset('/theme/assets/img/logo-sign.png')}}" alt="Logo" width="500px" height="auto" style="margin-bottom: -50px margin-left:20px; !important;">
</div>
<div class="login-right">
<div class="login-right-wrap">
<!-- <h1 style="font-family: 'SF UI Text', sans-serif !important; font-weight: 700;"> Taam Tasks Tracker</h1> -->
<img src="{{asset('/Logo/logo.jpg')}}" alt="" width="320px">
<!-- <p class="account-subtitle"> Projects Tasks Tracker</p> -->
<h5 class="mt-3" style="font-family: 'SF UI Text', sans-serif !important;">New Password</h5>
<form method="POST" action="{{ route('password.store') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">
<div class="form-group">
<label>Email <span class="login-danger">*</span></label>
<input class="form-control" type="email" value="{{old('email', $request->email)}}" name="email" readonly required>
<span class="profile-views"><i data-lucide="circle-user"></i></span>
</div>
<div class="form-group">
<label>Password <span class="login-danger">*</span></label>
<input class="form-control pass-input" type="password" name="password" required>
<span class="profile-views feather-eye toggle-password"></span>
</div>
<div class="form-group">
<label>Confirm Password <span class="login-danger">*</span></label>
<input class="form-control pass-input" type="password" name="password_confirmation" required>
<span class="profile-views feather-eye toggle-password"></span>
</div>
<div class="forgotpass">
</div>
<div class="form-group">
<button class="btn btn-primary btn-block" type="submit">Reset Password</button>
</div>
</form>
</div>
</div>
</div>
</div>
</div>
</div>


<script src="{{asset('/theme/assets/js/jquery-3.6.0.min.js')}}"></script>
<script src="{{asset('/theme/assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('/theme/assets/js/feather.min.js')}}"></script>
<script src="{{asset('/theme/assets/js/script.js')}}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get all toast messages
        const toasts = document.querySelectorAll('.toast-message');

        // Define sounds for success and error
        const successSound = new Audio('{{asset('/theme/assets/sounds/toastSound.mp3')}}'); // Success sound
        const errorSound = new Audio('{{asset('/theme/assets/sounds/error.mp3')}}'); // Error sound

        // Show each toast and start the fade-in effect
        toasts.forEach(function(toast) {
            toast.style.opacity = '1';
            toast.style.visibility = 'visible';

            // Play the corresponding sound based on the toast type (success or error)
            if (toast.classList.contains('success')) {
                successSound.play();
            } else if (toast.classList.contains('error')) {
                errorSound.play();
            }

            // Automatically start fading out after 5 seconds
            setTimeout(function() {
                toast.style.transition = 'opacity 0.5s ease'; // Fade-out transition
                toast.style.opacity = '0'; // Fade out

                // After the fade-out, completely hide or remove the toast
                setTimeout(function() {
                    toast.style.visibility = 'hidden'; // Optionally remove the toast from DOM
                    toast.remove(); // Remove element to prevent it from flashing back
                }, 500); // Wait for the fade-out to finish (0.5 seconds)
            }, 2500); // Start fade-out after 4.5 seconds
        });
    });
</script>
</body>
</html>