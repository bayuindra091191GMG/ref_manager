<!doctype html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

    @yield('title')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Icons -->
    <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
    <link rel="shortcut icon" href="{{ asset('frontend/media/favicons/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192"
      href="{{ asset('frontend/media/favicons/android-icon-192x192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('frontend/media/favicons/apple-icon-180x180.png') }}">
    <!-- END Icons -->

    <!-- Stylesheets -->
    <!-- Fonts and OneUI framework -->
    @yield('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css') }}">

    <!-- You can include a specific file from css/themes/ folder to alter the default color theme of the template. eg: -->
    <!-- <link rel="stylesheet" id="css-theme" href="assets/css/themes/amethyst.min.css"> -->
    <!-- END Stylesheets -->

    <style>
      body {
        font-size: 0.75rem;
      }

      .text-red {
        color: red;
      }

      .table thead th {
        font-size: .8rem;
      }

      .nav-main-link {
        font-size: .8rem;
      }

      #main-content {
        width: 100% !important;
      }

      #page-container.side-scroll #sidebar .content-header,
      #page-container.side-scroll #sidebar .content-side {
        width: 220px !important;
      }

      .btn {
        font-size: 0.75rem;
      }

      .form-select {
        font-size: 0.75rem;
      }

      .form-control {
        font-size: 0.75rem;
      }

      .col-form-label {
        font-size: 0.75rem;
      }

      .input-group-text {
        font-size: 0.8rem;
      }
    </style>
  </head>

  <body>
    <!-- Page Container -->
    <div id="page-container" class="page-header-dark main-content-boxed">

      <!-- Header -->
      {{--    @include('partials.admin._header') --}}
      <!-- END Header -->

      <!-- Main Container -->
      <main id="main-container">

        {{-- <!-- Navigation -->
        @if (\Illuminate\Support\Facades\Auth::guard('admin')->user()->branch_id === 6)
          @include('partials.admin._navigation_oura')
        @else
          @include('partials.admin._navigation')
        @endif --}}

        <!-- END Navigation -->
        <!-- Page Content -->
        @yield('content')
        <!-- END Page Content -->
      </main>
      <!-- END Main Container -->

      <!-- Footer -->
      {{-- @include('partials.admin._footer') --}}
      <!-- END Footer -->
    </div>
    <!-- END Page Container -->

    <!--
    OneUI JS

    Core libraries and functionality
    webpack is putting everything together at assets/_js/main/app.js
-->
    <script src="{{ asset('js/oneui.app.min.js') }}"></script>

    @yield('scripts')
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
    </script>
  </body>

</html>
