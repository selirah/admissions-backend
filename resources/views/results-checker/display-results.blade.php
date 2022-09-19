@extends ('layouts.layout')
@section('content')
<div class="container">
    @if($results->isNotEmpty())
    <div class="clearfix mt-3">
        <button class="btn btn-primary" style="float: right" onclick="PrintResult('{{URL::to('/')}}/print-results?year={{$year}}&semester={{$semester}}')"><i class="fa fa-print"></i>Print Results</button>
    </div>
    @endif
    <h5 class="mt-3 p-sm-2" style="background-color: #333333; color: #fff;">Year {{$year}}, Semester {{$semester}} Examination Results</h5>
    @if($results->isNotEmpty())
        <img style="max-width: 130px; display: block; margin: 0 auto" src="{{$results[0]->logo}}" alt="logo">
    <h5 class="text-uppercase text-center mb-0">{{$results[0]->school_name}}</h5>
    <h6 style="font-size: 90%" class="text-center mb-0">{{$results[0]->address}}, {{$results[0]->town}}, {{$results[0]->region}}</h6>
    <p style="font-size: 80%" class="text-center">Tel: {{$results[0]->phone}} | Email: {{$results[0]->email}}</p>
        <hr>
    <div class="col-sm-6">
        <table style="font-size: 80%; width: 100%">
            <tr>
                <th style="width: 120px">Index Number: </th>
                <td>{{empty($results[0]->index_no) ? 'Not Available' : $results[0]->index_no}}</td>
                <th>Level / Year:</th>
                <td>Year {{$year}}</td>
            </tr>
            <tr>
                <th>Programme: </th>
                <td>{{$results[0]->programme}}</td>
                <th>Semester: </th>
                <td>Semester {{$semester}}</td>
            </tr>
        </table>
    </div>
    <table style="font-size: 80%" class="table table-striped table-bordered mt-5">
        <thead>
        <tr>
            <th>COURSE CODE</th>
            <th>COURSE NAME</th>
            <th>GRADE</th>
        </tr>
        </thead>

        <tbody id="results">
        @foreach($results as $result)
        <tr>
            <td>{{$result->course_code}}</td>
            <td>{{$result->course}}</td>
            <td>{{$result->grade}}</td>
        </tr>
        @endforeach
        </tbody>

    </table>
    @else
    <p class="text-center mt-5 text-danger">Results have not been published for the selected Year and Semester</p>
    @endif
</div>
@endsection
