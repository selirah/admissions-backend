@extends ('layouts.auth')

@section ('content')
    <h5 class="card-title text-center">Login</h5>
    <form class="form-signin" name="register-form" method="post" action="{{ route('session.login') }}">
        {{ csrf_field()  }}

        @if(\Illuminate\Support\Facades\Session::has('flash_message'))
            <div class="alert {{ \Illuminate\Support\Facades\Session::get('flash_type') }}">
                {{ \Illuminate\Support\Facades\Session::get('flash_message') }}
            </div>
        @endif
        <div class="form-group{{ $errors->has('app_number') ? ' has-error' : '' }}">
            <label for="app_id">Application Number</label>
            <input type="text" id="app_id" class="form-control" name="app_number" {!! old('app_number') ? 'value="' . old('app_number') . '"' : '' !!}>
            @if ($errors->has('app_number'))
                <span class="help-block" style="color: #ea4335">{{ $errors->first('app_number') }}</span>
            @endif
        </div>

        <div class="form-group{{ $errors->has('pin') ? ' has-error' : '' }}">
            <label for="pin">Pin</label>
            <input type="password" id="pin" class="form-control" name="pin">
            @if ($errors->has('pin'))
                <span class="help-block" style="color: #ea4335">{{ $errors->first('pin') }}</span>
            @endif
        </div>
        <button class="btn btn-lg btn-primary btn-block text-uppercase" type="submit">Sign in</button>
    </form>
@endsection
