<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admission System</title>
    @include('includes.head')
</head>
<body class="d-flex flex-column h-100">
@include('includes.nav')
<!-- Begin page content -->
<main role="main" class="flex-shrink-0">
    <div class="container">
        <div class="row">
            @yield('content')
        </div>
    </div>
</main>
{{--@include('includes.foot')--}}
@include('includes.footer')
</body>
</html>
