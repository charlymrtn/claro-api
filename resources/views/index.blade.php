<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ClaroPagos - API</title>

        <script type="text/javascript" src="{{ mix('/js/mix/ui.js') }}"></script>
        <link rel="stylesheet" type="text/css" href="{{ mix('/css/mix/ui.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ mix('/css/vendor.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ mix('/css/app.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/login.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('vendor/adminlte/plugins/iCheck/square/blue.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('vendor/adminlte/css/auth.css') }}">
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="links">
                    API
                </div>

                <div class="title m-b-md">
                    Claro Pagos
                </div>
            </div>
        </div>

       <!-- Scripts -->
    </body>
</html>
