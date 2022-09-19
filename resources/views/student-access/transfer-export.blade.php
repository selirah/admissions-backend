<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Requests</title>
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
        <h5>LIST OF INCOMING TRANSFER REQUESTS</h5>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>APP. NUMBER</th>
                <th>SURNAME</th>
                <th>OTHERNAMES</th>
                <th>PHONE</th>
                <th>NEW SCHOOL</th>
                <th>APPROVE (SIGN)</th>
            </tr>
        </thead>
        <tbody>
            <?php $count = 1; ?>
            @foreach ($trans as $transfer)
                <tr>
                    <td>{{ $count++ }}</td>
                    <td>{{ $transfer->application_number }}</td>
                    <td>{{ $transfer->surname }}</td>
                    <td>{{ $transfer->other_names }}</td>
                    <td>{{ $transfer->phone }}</td>
                    <td>{{ $transfer->destination_school_name }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
