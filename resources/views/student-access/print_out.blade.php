<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title> {{ $school->school_name }} | Print Letter Page</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}" type="text/css">
    <link href="{{ URL::asset('css/quill.snow.css') }}" rel="stylesheet" type="text/css" />
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
        <div class="heads mt-5" id="topmenu" style="text-align:right;">
            <button type="button" class="btn btn-primary btn-sm pull-right" onclick="window.print();"
                id="print_button"><i class="fa fa-print"></i> PRINT
            </button>
        </div>

        <div class="row">
            <div class="col-12 mt-5 ql-container">
                <div style="width: 100%">
                    <img src="{{ $document->letter_head }}" alt="letter head" width="100%" />
                </div>
                <hr />
                <div class="ql-editor" style="margin-top: 10px; text-align: justify; text-justify: inter-word;">
                    <?php
                    $fee = !empty($fees) ? $fees->amount : '';
                    $placeholders = ['[app_number]', '[hall]', '[programme]', '[phone]', '[date]', '[fee]', '[name]', '[academic_year]'];
                    $values = [$student->application_number, strtoupper($student->hall), strtoupper($programme), $student->phone, date('F jS, Y', strtotime($student->created_at)), $fee, $student->other_names . ' ' . $student->surname, $student->academic_year];
                    
                    $body = str_replace($placeholders, $values, $letter->admission);
                    ?>
                    {!! $body !!}

                </div>
                <p>
                    Accept our congratulations.
                </p>
                <div class="col-12 mt-3">
                    <p>
                        <img height="100px" src="{{ $school->letter_signature }}" alt="signature" />
                    </p>
                </div>
                <div class="col-12 mt-3">
                    <p><strong>{{ strtoupper($school->letter_signatory) }}</strong></p>
                </div>
                <div class="col-12 mt-3 mb-2">
                    <p><strong>({{ strtoupper($school->signatory_position) }})</strong></p>
                </div>
                @if (!empty($document->letter_footer))
                    <div style="width: 100%; height: 100%; position: relative;">
                        <img src="{{ $document->letter_footer }}" alt="letter footer" width="100%"
                            style="position: fixed; bottom: 0px; left: 0px;" />
                    </div>
                @endif
            </div>
            @if (!empty($letter->acceptance))
                <div class="col-12 mt-5 ql-container page-break">

                    <div class="ql-editor" style="margin-top: 20px; text-align: justify; text-justify: inter-word"
                        class="page-break">
                        <?php
                        $fee = !empty($fees) ? $fees->amount : '';
                        $placeholders = ['[app_number]', '[hall]', '[programme]', '[phone]', '[date]', '[fee]', '[name]', '[academic_year]'];
                        $values = [$student->application_number, strtoupper($student->hall), strtoupper($programme), $student->phone, date('F jS, Y', strtotime($student->created_at)), $fee, $student->other_names . ' ' . $student->surname, $student->academic_year];
                        
                        $body = str_replace($placeholders, $values, $letter->acceptance);
                        ?>
                        {!! $body !!}
                    </div>
                    @if (!empty($document->letter_footer))
                        <div style="width: 100%; height: 100%; position: relative;">
                            <img src="{{ $document->letter_footer }}" alt="letter footer" width="100%"
                                style="position: fixed; bottom: 0px; left: 0px;" />
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</body>

</html>
