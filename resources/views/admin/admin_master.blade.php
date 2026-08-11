<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="POS - SAACID CLOUD">
 
    <meta name="author" content="Taam solutions - Saacid System">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Waraabe System - Dashboard')</title>

    <style>
        /* Prevent Flash of Unstyled Content (FOUC) */
        html {
            visibility: hidden;
            opacity: 0;
        }
        html.dom-ready {
            visibility: visible;
            opacity: 1;
            transition: opacity 0.15s ease-in;
        }
        #global-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            z-index: 999999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        #global-loader.loader-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        .whirly-loader {
            width: 48px;
            height: 48px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #3d5ee1;
            border-radius: 50%;
            animation: global-loader-spin 0.8s linear infinite;
        }
        @keyframes global-loader-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        (function() {
            function showPage() {
                document.documentElement.classList.add('dom-ready');
                var l = document.getElementById('global-loader');
                if (l && !l.classList.contains('loader-hidden')) {
                    l.classList.add('loader-hidden');
                    setTimeout(function() { l.style.display = 'none'; }, 300);
                }
            }
            if (document.readyState === 'interactive' || document.readyState === 'complete') {
                showPage();
            } else {
                document.addEventListener('DOMContentLoaded', showPage);
                window.addEventListener('load', showPage);
            }
            setTimeout(showPage, 400); // Safety fallback
        })();
    </script>

    @include('partials.pwa-head')

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('Logo/icon.png') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/twitter-bootstrap-wizard/form-wizard.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>
<style>
    @font-face {
        font-family: 'SF UI Text';
        src: url('{{ asset('/assets/fonts/SFUI/SFUIText-Regular.ttf') }}') format('truetype');
        font-weight: 400;
        font-style: normal;
    }

    /* Import SF UI Text Bold */
    @font-face {
        font-family: 'SF UI Text';
        src: url('{{ asset('/assets/fonts/SFUI/SFUIText-Bold.ttf') }}') format('truetype');
        font-weight: 700;
        font-style: normal;
    }

    * {
        font-family: 'SF UI Text', Arial, sans-serif;
        /* font-family: "Poppins", sans-serif; */
        font-weight: 400;
        font-style: normal;
    }

    body {
        font-family: 'SF UI Text', sans-serif !important;
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
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        /* Subtle shadow for depth */
        border: 0.25px solid;
        max-width: 400px;
        font-family: Arial, sans-serif;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.5s ease-in-out;
        /* Fade-in/out transition */
    }

    /* Success message design */
    .toast-message.success {
        border-color: #2d8b41;
        /* Green border for success */
        background-color: #fff;
        /* Light green background */
        color: #2d8b41;
        /* Dark green text */
    }

    /* Error message design */
    .toast-message.error {
        border-color: #c0392b;
        /* Red border for error */
        background-color: #fff;
        /* Light red background */
        color: #c0392b;
        /* Dark red text */
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
        background-color: #eefced;
        /* Light green circle for success */
    }

    .toast-message.error .toast-icon {
        background-color: #fceded;
        /* Light red circle for error */
    }

    .icon-checkmark,
    .icon-error {
        font-size: 26px !important;
        /* Make the icon bigger */
    }

    .icon-checkmark {
        color: #31C75F;
        /* Green checkmark */
    }

    .icon-error {
        color: #fa0707;
        /* Red exclamation */
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
        font-family: 'SF UI Text', Arial, sans-serif !important;
    }

    .toast-content p {
        margin: 0;
        color: black;
        font-family: 'SF UI Text', Arial, sans-serif !important;
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
    <div id="global-loader">
        <div class="whirly-loader"> </div>
    </div>

    <div class="main-wrapper">

        <!-- Header Start -->
        @include('admin.inc.header')
        <!-- End Header -->


        <!-- Sidebar Start -->
        @include('admin.inc.sidebar')
        <!-- End Sidebar -->

        <!-- Start Page Content -->
        @yield('admin')
        <!-- End Page Content -->
    </div>




    <!-- <script src="{{ asset('/assets/js/jquery-3.6.0.min.js') }}"></script> -->
    <script src="{{ asset('assets/js/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/twitter-bootstrap-wizard/prettify.js') }}"></script>
    <script src="{{ asset('assets/plugins/twitter-bootstrap-wizard/form-wizard.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/sweetalerts.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/apexchart/chart-data.js') }}"></script>
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all toast messages
            const toasts = document.querySelectorAll('.toast-message');

            // Define sounds for success and error
            const successSound = new Audio('{{ asset('/theme/assets/sounds/toastSound.mp3') }}'); // Success sound
            const errorSound = new Audio('{{ asset('/theme/assets/sounds/error.mp3') }}'); // Error sound

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
                        toast.style.visibility =
                            'hidden'; // Optionally remove the toast from DOM
                        toast.remove(); // Remove element to prevent it from flashing back
                    }, 500); // Wait for the fade-out to finish (0.5 seconds)
                }, 4500); // Start fade-out after 4.5 seconds
            });
        });
    </script>
</body>

</html>