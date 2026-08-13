<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="author" content="Taam Solutions">
    <meta name="Platform" content="Saacid">
    <title>Saacid - Login</title>
    <link rel="shortcut icon" href="{{asset('/Logo/icon.png')}}">
    <!-- Font Awesome -->
    <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    rel="stylesheet"
    />
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    
<!-- MDB -->
<link
  href="https://cdn.jsdelivr.net/npm/mdb-ui-kit@8.2.0/css/mdb.min.css"
  rel="stylesheet"
/>
<link rel="stylesheet" href="{{asset('uniquestyle/message.css')}}">
@include('partials.icons')
</head>
<style>
    body {
        font-family: "Inter", sans-serif;
    }
    .gradient-custom-2 {
    /* Fallback for older browsers */
    background: #1e1873;

    /* Chrome 10-25, Safari 5.1-6 */
    background: -webkit-linear-gradient(to right, #1e1873, #217318);

    /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
    background: linear-gradient(to right, #1e1873, #217318);
    }

    @media (min-width: 768px) {
    .gradient-form {
    height: 100vh !important;
    }
    }
    @media (min-width: 769px) {
    .gradient-custom-2 {
    border-top-right-radius: .3rem;
    border-bottom-right-radius: .3rem;
    }
    }
    
    /* New styles for centering the form */
    .centered-form-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }
    .centered-form-card {
        width: 100%;
        max-width: 500px;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
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
    <section class="gradient-form" style="background-color: #eee;">
        <div class="centered-form-container">
            <div class="centered-form-card card rounded-3 text-black">
                <div class="card-body p-md-5 mx-md-4">
                    <div class="text-center">
                        <img src="{{asset('/Logo/Logo1.png')}}"
                          style="width: 200px; height: 150px;" alt="logo">
                    </div>
      
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <p class="mt-3" style="font-family: 'Inter';">It takes just seconds, Let's know it's you 😎!</p>
                        <div data-mdb-input-init class="form-outline mb-4">
                          <input type="text" name="username" id="form2Example11" class="form-control"
                            placeholder="Username" />
                          <label class="form-label" for="form2Example11">Username</label>
                        </div>
      
                        <div data-mdb-input-init class="form-outline mb-4 position-relative">
                            <input type="password" name="password" id="form2Example22" class="form-control" />
                            <label class="form-label" for="form2Example22">Password</label>
                            <span class="toggle-password" onclick="togglePassword()" style="cursor: pointer; position: absolute; right: 15px; top: 50%; transform: translateY(-50%);">
                                <i data-lucide="eye" id="toggleEye"></i>
                            </span>
                        </div>                        
      
                        <div class="text-center pt-1 mb-5 pb-1">
                          <button class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3" type="submit">Log
                            in</button>
                        </div>
                        <center>
                            <!--<img src="{{asset('Logo/logo.jpg')}}" alt="" width="150">-->
                            <hr>
                            <p class="small text-muted">Powered By <a target="_blank" href="https://saacid.taamsolutions.net">Taam Solutions</a> - All Rights Reserved | {{date('Y')}} <br> <span style="font-family: 'SF UI Text'">Saacid V2.0 - For Waraabe Management System</span></p>
                        </center>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- MDB -->
    <script
      type="text/javascript"
      src="https://cdn.jsdelivr.net/npm/mdb-ui-kit@8.2.0/js/mdb.umd.min.js"
    ></script>
    <script>
        function togglePassword() {
            var passwordField = document.getElementById("form2Example22");
            var revealing = passwordField.type === "password";

            passwordField.type = revealing ? "text" : "password";
            window.setIcon("#toggleEye", revealing ? "eye-off" : "eye");
        }

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
