<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title> {{ $school->school_name }} | Print Examination Result</title>
    @include('includes.head')
    <style type="text/css" media="print">
        body {
            background-color: #ffffff;
            zoom: 100%;
        }

        @media print {
            a[href]:after {
                content: none !important;
            }
        }

        @page {
            size: A4;
            /* auto is the current printer page size */
        }

        table {
            width: 100%;
        }

        td {
            vertical-align: top;
        }

        p {
            margin: 2px 0;
            font-size: 13px;
        }

        .justify {
            text-align: justify;
            text-justify: inter-word;
        }

        strong {
            color: #000000;
        }

        .btn {
            margin-left: 5px;
        }

        #topmenu {
            padding: 10px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(34, 36, 38, .15);
            display: none;
        }

        .page-break {
            page-break-before: always;
        }

        ol,
        ul {
            list-style: decimal;
        }

        td {
            padding: 5px;
        }

        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 80px;
        }

        hr {
            display: block;
            height: 1px;
            background: transparent;
            width: 100%;
            border: none;
            border-top: solid 1px #aaa;
        }

    </style>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
            integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>
    <script src="{{ URL::asset('js/script.js') }}" type="application/javascript"></script>
</head>
<!-- END HEAD -->

<body>

<div>


    @if($results->isNotEmpty())
    <div class="container">
        <div class="container">
            <div class="heads mt-5" id="topmenu" style="text-align:right;">
                <button type="button" class="btn btn-primary btn-sm pull-right" onclick="window.print();"
                        id="print_button"><i class="fa fa-print"></i> PRINT
                </button>
            </div>
        </div>
        @endif
        @if($results->isNotEmpty())
            <img style="max-width: 130px; display: block; margin: 0 auto" src="{{$results[0]->logo}}" alt="logo">
            <h5 class="text-uppercase text-center mb-0">{{$results[0]->school_name}}</h5>
            <h6 style="font-size: 90%" class="text-center mb-0">{{$results[0]->address}}, {{$results[0]->town}}, {{$results[0]->region}}</h6>
            <p style="font-size: 80%" class="text-center">Tel: {{$results[0]->phone}} | Email: {{$results[0]->email}}</p>
            <hr>
            <div class="col-sm-6">
                <table style="font-size: 80%; width: 100%" class="tsable table-condensed tables-striped">
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
</div>
</body>

</html>
