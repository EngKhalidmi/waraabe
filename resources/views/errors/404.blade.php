<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<meta name="description" content="POS - Bootstrap Admin Template">
<meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern,  html5, responsive">
<meta name="author" content="Dreamguys - Bootstrap Admin Template">
<meta name="robots" content="noindex, nofollow">
<title>Saacid | 404 </title>

<link rel="shortcut icon" type="image/x-icon" href="{{asset('/Logo/icon.png')}}">

<link rel="stylesheet" href="{{asset('/assets/css/bootstrap.min.css')}}">

<link rel="stylesheet" href="{{asset('/assets/css/animate.css')}}">

<link rel="stylesheet" href="{{asset('/assets/css/dataTables.bootstrap4.min.css')}}">

<link rel="stylesheet" href="{{asset('/assets/plugins/fontawesome/css/fontawesome.min.css')}}">
<link rel="stylesheet" href="{{asset('/assets/plugins/fontawesome/css/all.min.css')}}">
<link rel="stylesheet" href="{{asset('/assets/css/style.css')}}">
@include('partials.icons')
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
    body{
            font-family: 'SF UI Text', sans-serif !important;
        }
</style>
<body class="error-page">
<div id="global-loader">
<div class="whirly-loader"> </div>
</div>

<div class="main-wrapper">
<div class="error-box">
<h1>404</h1>
<h3 class="h2 mb-3"><i data-lucide="circle-alert"></i> Oops! Page Not Found!</h3>
<p class="h4 font-weight-normal">The page you requested was not found.</p>
<a href="{{route('dashboard')}}" class="btn btn-primary">Back to Home</a>
<hr>
<p class="mb-0">Powered by <a href="https://saacid.taamsolutions.net" target="_blank">Taam Solutions</a></p>
</div>
</div>


<script src="{{asset('assets/js/jquery-3.6.0.min.js')}}"></script>

<script src="{{asset('assets/js/feather.min.js')}}"></script>

<script src="{{asset('assets/js/jquery.slimscroll.min.js')}}"></script>

<script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>

<script src="{{asset('assets/js/script.js')}}"></script>
</body>
</html>