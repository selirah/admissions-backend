@extends ('layouts.layout')
@section('content')

    <div class="container" style="display: flex; align-items: center; justify-content: center; height: calc(100vh - 76px);">
        <div class="row">
            <div class="col-sm-12 mb-5 text-center">
                <h1>What do you want to do?</h1>
            </div>
            <div class="col-sm-6 mt-3">
                <a href="{{ URL::to('/home') }}" class="btn btn-block btn-primary btn-large">Print / Re-print Admission Letter</a>
            </div>
            <div class="col-sm-6 mt-3">
                <a href="{{ URL::to('/results-checker') }}" class="btn btn-block btn-success btn-large">Check Examination Results</a>
            </div>
        </div>
    </div>

@endsection
