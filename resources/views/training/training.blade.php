<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Training List </title>
    <link href="{{ public_path('css/pdf.css') }}" rel="stylesheet" type="text/css" />

</head>

<body>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>SCHOOL NAME</th>
                <th>REGION</th>
                <th>LOCATION</th>
                <th>DATE</th>
                <th>FIRST REP</th>
                <th>PHONE</th>
                <th>SECOND REP</th>
                <th>PHONE</th>
            </tr>
        </thead>
        <tbody>
            <?php $count = 1; ?>
            @foreach ($trainings as $training)
                <tr>
                    <td>{{ $count++ }}</td>
                    <td>{{ $training['school_name'] }}</td>
                    <td>{{ $training['region'] }}</td>
                    <td>{{ $training['location'] }}</td>
                    <td>{{ $training['date_time'] }}</td>
                    <td>{{ $training['name_one'] }}</td>
                    <td>{{ $training['phone_one'] }}</td>
                    <td>{{ $training['name_two'] }}</td>
                    <td>{{ $training['phone_two'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
