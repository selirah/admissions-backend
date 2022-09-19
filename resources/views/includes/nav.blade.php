<header>
    <!-- Fixed navbar -->
    <nav class="navbar navbar-expand-md navbar-light fixed-top bg-light">
        <a class="navbar-brand" href="{{ URL::to('/') }}">Admissions Ghana</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav mr-auto">
                @if(session ('isNewStudent'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ URL::to('/') }}">Home</a>
                </li>
                @endif
                <li class="nav-item active">
                    <a class="nav-link"
                        href="#">{{ \Illuminate\Support\Facades\Session::get('name') }}
                        <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ URL::to('/logout') }}">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
</header>
