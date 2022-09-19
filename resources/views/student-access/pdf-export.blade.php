<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> {{ $school->school_name }} | Print</title>
    <link href="{{ public_path('css/pdf.css') }}" rel="stylesheet" type="text/css" />

</head>

<body>
    <div class="header-wrapper">
        <h2>{{ $school->school_name }}</h2>
        <h4>{{ $school->town }}, {{ $school->region }} REGION</h4>
        <h4>TEL: {{ $school->phone }}</h4>
        <h4>ACADEMIC YEAR: {{ $school->academic_year }}</h4>
    </div>
    <div class="header-wrapper">
        <h5>{{ $header }}</h5>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>APPLICATION NUMBER</th>
                <th>SURNAME</th>
                <th>OTHERNAMES</th>
                <th>PROGRAMME</th>
                <th>STATUS</th>
                <th>ACADEMIC YEAR</th>
                <th>PHONE</th>
                <th>HALL</th>
            </tr>
        </thead>
        <tbody>
            <?php $count = 1; ?>
            @foreach ($stds as $student)
                <tr>
                    <td>{{ $count++ }}</td>
                    <td>{{ $student['application_number'] }}</td>
                    <td>{{ $student['surname'] }}</td>
                    <td>{{ $student['other_names'] }}</td>
                    <td>{{ $student['programme'] }}</td>
                    <td>{{ $student['status'] }}</td>
                    <td>{{ $student['academic_year'] }}</td>
                    <td>{{ $student['phone'] }}</td>
                    <td>{{ $student['hall'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
