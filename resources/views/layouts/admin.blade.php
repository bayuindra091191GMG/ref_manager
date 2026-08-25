<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

    @yield('title')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description" content="Simi Indonesia">
    <meta name="author" content="Simi Indonesia">
    <meta name="robots" content="noindex, nofollow">

    <!-- Open Graph Meta -->
    <meta property="og:title" content="Simi Indonesia - Backend Login">
    <meta property="og:site_name" content="Simi Indonesia">
    <meta property="og:description" content="Simi Indonesia - Backend Login">
    <meta property="og:type" content="website">
    <meta property="og:url" content="">
    <meta property="og:image" content="">

    <!-- Icons -->
    <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
    <link rel="shortcut icon" href="{{ asset('images/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicons/android-icon-192x192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/favicons/apple-icon-180x180.png') }}">
    <!-- END Icons -->

    <!-- Stylesheets -->
    <!-- Fonts and OneUI framework -->
    @yield('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" id="css-main" href="{{ asset('css/custom.css')}}">
{{--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">--}}
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">

    <!-- You can include a specific file from css/themes/ folder to alter the default color theme of the template. eg: -->
    <!-- <link rel="stylesheet" id="css-theme" href="assets/css/themes/amethyst.min.css"> -->
    <!-- END Stylesheets -->

    <style>
        body{
            font-size: 0.75rem;
        }
        .text-red{
            color: red;
        }
        .table thead th{
            font-size: .8rem;
        }
        .nav-main-link{
            font-size: .8rem;
        }
        #main-content{
            width: 100% !important;
        }
        #page-container.side-scroll #sidebar .content-header, #page-container.side-scroll #sidebar .content-side{
            /*width: 220px !important;*/
        }
        .btn{
            font-size: 0.75rem;
        }
        .form-select{
            font-size: 0.75rem;
        }
        .form-control{
            font-size: 0.75rem;
        }
        .col-form-label{
            font-size: 0.75rem;
        }
        .input-group-text{
            font-size: 0.8rem;
        }
        .datepicker.datepicker-dropdown.dropdown-menu {
            width: inherit;
            max-height: max-content !important;
        }

        [v-cloak] {
            display: none;
        }
    </style>
</head>
<body>
<!-- Page Container -->
<div id="page-container" class="sidebar-o enable-page-overlay side-scroll page-header-fixed main-content-narrow">

    <!-- Header -->
{{--    @include('partials.admin._header')--}}
    <!-- END Header -->

    <!-- Main Container -->
    <main id="main-container">

        <!-- Navigation -->
{{--        @if(\Illuminate\Support\Facades\Auth::guard('admin')->user()->branch_id === 6)--}}
{{--            @include('partials.admin._navigation_oura')--}}
{{--        @else--}}
{{--        @endif--}}
        @include('partials.admin._navigation')

        <!-- END Navigation -->
        <!-- Page Content -->
        @yield('content')
        <!-- END Page Content -->
    </main>
    <!-- END Main Container -->

    <!-- Footer -->
    @include('partials.admin._footer')
    <!-- END Footer -->
</div>
<!-- END Page Container -->

<!--
    OneUI JS

    Core libraries and functionality
    webpack is putting everything together at assets/_js/main/app.js
-->

<script>
    function makeid(length) {
        let result = '';
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        const charactersLength = characters.length;
        let counter = 0;
        while (counter < length) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
            counter += 1;
        }
        return result;
    }

    function debounce(func, timeout = 300){
        let timer;
        clearTimeout(timer);
        timer = setTimeout(func, timeout);
    }

</script>
<script src="{{ asset('js/oneui.app.min.js') }}"></script>
@yield('scripts')
</body>
</html>
