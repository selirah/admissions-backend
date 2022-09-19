<!DOCTYPE html>
<html>
<head>
    <title>Admission System</title>
    @include('includes.head')
</head>
<body class="login-body">
<div class="container">
    <div class="row">
        <div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
            <div class="card card-signin" style="margin-top: 200px">
                <div class="card-body">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>
</body>
@include('includes.footer')
</html>
