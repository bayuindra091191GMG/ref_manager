<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

    <title>Voucher - {{ $voucher->code }}</title>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description" content="Simi Indonesia">
    <meta name="author" content="Simi Indonesia">
    <meta name="robots" content="noindex, nofollow">

    <!-- Open Graph Meta -->
    <meta property="og:title" content="Simi Indonesia">
    <meta property="og:site_name" content="Simi Indonesia">
    <meta property="og:description" content="Simi Indonesia">
    <meta property="og:type" content="website">
    <meta property="og:url" content="">
    <meta property="og:image" content="">

    <!-- Icons -->
    <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
    <link rel="shortcut icon" href="{{ asset('images/logo_karada_orange.webp') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/logo_karada_orange.webp') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo_karada_orange.webp') }}">
    <!-- END Icons -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
<div class="d-flex flex-column align-items-center">
   <div style="width: 100%; max-width: 480px">
       @if($voucher->user_voucher->coupon->img_path)
           <img src="{{ asset("images/coupons/{$voucher->user_voucher->coupon->img_path}") }}" style="width: 100%">
       @endif

       <div class="d-flex flex-column p-3 justify-content-center card">
           <h5 class="m-0 text-center fw-bold">{{ $voucher->user_voucher->voucher_code }}</h5>
           <p class="m-0 text-center fs-sm">Expired at : {{ \Carbon\Carbon::parse($voucher->expired_date)->format('d F Y') }}</p>
       </div>

       <div class="d-flex flex-column p-3 justify-content-center" style="background-color: #547155">
           <p class="m-0 text-center text-white fs-sm">Voucher Code</p>
           <h4 class="m-0 text-center fw-bold text-white">{{ $voucher->code }}</h4>
       </div>

           <div class="card accordion-wrapper"><!----><!----><header data-v-19681d29="" role="tab" class="card-header accordion-header not-collapsed" tabindex="0" aria-expanded="true" aria-controls="accordion0" style="overflow-anchor: none;"><span data-v-19681d29="" class="accordion-title">Syarat &amp; Ketentuan</span> </header> <div data-v-19681d29="" id="accordion0" class="collapse show" role="tabpanel"><div data-v-19681d29="" class="card-body"><!----><!----><div data-v-19681d29="" class="accordion-text"><p>Salam hangat dari Simi Indonesia,</p>

                           <p>Dengan hormat, kami mengundang Anda untuk melakukan body treatment dengan teknik khusus dari Jepang, yang kami beri nama "AP Balance Treatment".</p>

                           <p>Simi didirikan pada tahun 2001 di Yokohama (Jepang) dan telah memiliki lebih dari 360 outlet di seluruh dunia, termasuk 7&nbsp;outlet di Indonesia :</p>

                           <ul>
                               <li>Simi Taman Anggrek (0811 190 8781)</li>
                               <li>Simi PIK Avenue (0811 190 8783)</li>
                               <li>Simi Citywalk Sudirman (0811 190 8784)</li>
                               <li>Simi Living World Alam Sutera (0811 190 8785)</li>
                               <li>Simi Plaza Indonesia (0811 190 8782)</li>
                               <li>Simi Mall of Indonesia (0811 92&nbsp;8786)</li>
                               <li>Simi fX Sudirman (0811 589 8787)</li>
                           </ul>

                           <p>"AP Balance Treatment" merupakan sebuah teknik yang berfokus mengidentifikasi penyebab ketidak-nyamanan pada tubuh, dan memulihkannya.</p>

                           <p>Rasakan secara langsung manfaat dari "AP Balance Treatment", hanya dengan menunjukkan e-Voucher ini di salah satu outlet Simi Indonesia.</p>

                           <p>&nbsp;</p>

                           <p>Syarat &amp; Ketentuan:</p>

                           <ol>
                               <li>Harus divalidasi langsung oleh staf Simi Indonesia</li>
                               <li>Berlaku sesuai masa aktif tercantum</li>
                               <li>Berlaku setiap hari sesuai jam operasional outlet Simi Indonesia (Mohon untuk melakukan reservasi terlebih dahulu)</li>
                               <li>Berlaku di seluruh outlet Simi Indonesia</li>
                               <li>Tidak dapat digabungkan dengan promo/diskon lainnya</li>
                               <li>Tidak dapat dipecah menjadi 2 kali treatment / digunakan oleh 2 orang atau lebih</li>
                               <li>Syarat &amp; ketentuan dapat berubah sewaktu-waktu tanpa pemberitahuan terlebih dahulu</li>
                               <li>Jika terjadi kendala, pemegang e-voucher dapat menghubungi layanan CS di&nbsp;1500340&nbsp;atau&nbsp;Live chat</li>
                               <li>Info dan reservasi, dapat menghubungi nomor outlet yang ingin dikunjungi atau customer care Simi Indonesia : 0822 1144 7456 (pesan Whatsapp)</li>
                           </ol>

                           <p><br>
                               #SalamSehatBersamaSimi</p>
                       </div></div></div><!----><!----></div>
   </div>
</div>

<script src="{{ asset('js/oneui.app.min.js') }}"></script>
</body>
</html>
