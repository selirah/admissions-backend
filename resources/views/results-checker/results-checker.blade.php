@extends ('layouts.layout')
@section('content')
<div class="container">
    <div class="col-md-6 mt-5" style="margin: 0 auto">
        <h3 class="text-center mb-5 pb-3" style="border-bottom: 1px solid #ddd">Welcome to the Results Checker</h3>
        @if(\Illuminate\Support\Facades\Session::has('flash_message'))
            <div class="alert {{ \Illuminate\Support\Facades\Session::get('flash_type') }}">
                {{ \Illuminate\Support\Facades\Session::get('flash_message') }}
            </div>
        @endif
        <form action="{{URL::to('/results-checker/display-results')}}" method="post">
            @csrf
            <div class="form-group">
                <label for="year">Academic Year</label>
                <select class="form-control" name="year" id="year">
                    <option value="">- Please Select -</option>
                    <option value="1">Level 100</option>
                    <option value="2">Level 200</option>
                    <option value="3">Level 300</option>
                    <option value="4">Level 400</option>
                </select>
            </div>
            <div class="form-group">
                <label for="semester">Semester</label>
                <select class="form-control" name="semester" id="semester">
                    <option value="">- Please Select -</option>
                    <option value="1">Semester One (1)</option>
                    <option value="2">Semester Two (2)</option>
                </select>
            </div>
            <div class="form-group">
                <button name="check_results" type="submit" class="btn btn-primary">Check Results</button>
            </div>
        </form>
    </div>
</div>
@endsection
