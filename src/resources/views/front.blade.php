<!DOCTYPE html>
<html>
@if (app()->isLocale('ar'))
    <html direction="rtl" dir="rtl" style="direction: rtl" lang="ar">
@else
    <html direction="ltr" dir="ltr" style="direction: ltr" lang="en">
@endif

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="Majmaah University" content="mu.edu.sa">
    <meta name="description" content="System template.23">
    <title inertia> STR-AI</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ url('front/resources/master/images/logos/favicon.png') }}">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=Kumbh+Sans:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <!-- app CSS -->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('front/resources/master/vendor/font-awesome/css/all.min.css') }}" />

    <!-- Theme CSS -->
    @if (app()->isLocale('ar'))
        <link rel="stylesheet" type="text/css" href="{{ asset('front/resources/master/css/style-rtl.css') }} " />
    @else
    @endif


    <!-- Scripts -->
    @routes
    @vite(['resources/js/front.js', "resources/js/Front/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body>

    @inertia
    <script src="{{ asset('/front/resources/master/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <!--Template app-->
    <script src="/front/js/app.js"></script>
</body>

</html>
