<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}" type="text/css"> --}}
    <link href="{{ public_path('css/quill.snow.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ public_path('css/print.css') }}" rel="stylesheet" type="text/css" />
</head>

<body>

    <div class="container">
        <div class="row">
            <div class="col-12 mt-5 ql-container">
                <div style="width: 100%">
                    <img src="{{ public_path('uploads/letter_head/' . pathinfo($document->letter_head)['basename']) }}"
                        alt="letter head" width="100%" />
                </div>
                <hr style="height:1px;border:none;color:#eee;background-color:#eee;" />
                <div class="ql-editor" style="margin-top: 20px; text-align: justify; text-justify: inter-word">
                    <?php
                    $fee = !empty($fees) ? $fees->amount : '';
                    $placeholders = ['[app_number]', '[hall]', '[programme]', '[phone]', '[date]', '[fee]', '[name]', '[academic_year]'];
                    $values = [$student->application_number, strtoupper($student->hall), strtoupper($programme), $student->phone, date('F jS, Y', strtotime($student->created_at)), $fee, $student->other_names . ' ' . $student->surname, $student->academic_year];
                    
                    $body = str_replace($placeholders, $values, $letter->notice);
                    ?>
                    {!! $body !!}
                    <p class="mb-5">

                    </p>
                </div>
                <div class="col-12 mt-3">

                    <img height="100px"
                        src="{{ public_path('uploads/signatures/' . pathinfo($school->letter_signature)['basename']) }}"
                        alt="signature" />

                </div>
                <div class="col-12 mt-3">
                    <strong>{{ strtoupper($school->letter_signatory) }}</strong>
                </div>
                <div class="col-12 mt-3">
                    <strong>({{ strtoupper($school->signatory_position) }})</strong>
                </div>
                <hr style="height:1px;border:none;color:#eee;background-color:#eee;" />
                <div style="position: relative;">
                    <div class="footer">
                        <img src="{{ public_path('uploads/letter_footer/' . pathinfo($document->letter_footer)['basename']) }}"
                            alt="letter footer" width="100%" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
