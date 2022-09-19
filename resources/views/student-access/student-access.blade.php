@extends ('layouts.layout')
@section('content')

    <?php
    if(!session ('isNewStudent')){
        redirect ('/results-checker');
    }
    ?>

    <div class="col-md-12">
        @if (!$letter)
            <div class="container-fluid">
                <h1 class="display-4" style="color: #F44336">Your admission letter is NOT ready for printing</h1>
                <p class="lead">Please contact the school with this information</p>
            </div>

        @else
            @if ($student->status == env('STATUS_BLOCKED'))
                <div class="container-fluid text-center mt-5">
                    <h2 class="display-4" style="color: #F44336">Account Blocked</h1>
                        <p class="lead">Your account is blocked hence you cannot print admission letter. Contact
                            the
                            school for mire information</p>
                </div>
            @elseif ($school->fee_payment === 1 && $student->fee_receipt === 0)
                <div class="container-fluid text-center mt-5 mb-4">

                    @if (!empty($letter->notice))
                        <div class="col-12 mt-4">
                            <a href="{{ URL::to('/upload-receipt') }}" class="btn btn-success">Upload
                                Receipt</a>
                            <button class="btn btn-warning " onclick="PrintResult('/print-notice')">
                                Print
                            </button>
                        </div>
                        <div class="col-12 mt-5">
                            <div style="width: 100%">
                                <img src="{{ $document->letter_head }}" alt="letter head" width="100%" />
                            </div>
                            <hr style="height:1px;border:none;color:#eee;background-color:#eee;" />
                            <div style="margin-top: 20px; text-align: justify; text-justify: inter-word">
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
                            <div class="float-left">
                                <div class="col-12 mt-3">
                                    <p>
                                        <img height="100px" src="{{ $school->letter_signature }}" alt="signature" />
                                    </p>
                                </div>
                                <div class="col-12 mt-3">
                                    <p><strong>{{ strtoupper($school->letter_signatory) }}</strong></p>
                                </div>
                                <div class="col-12 mt-3">
                                    <p><strong>({{ strtoupper($school->signatory_position) }})</strong></p>
                                </div>
                            </div>
                        </div>


                    @else
                        <h1 class="display-4" style="color: #F44336">Oops..</h1>
                        <p class="lead">Kindly wait for information on fees before accessing the admission letter
                        </p>
                    @endif

                </div>
            @else
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-primary" onclick="PrintResult('/print-letter')"><i
                                class="fa fa-print"></i>
                            Print Letter
                        </button>
                        @if (!empty($document->docs))
                            <a target="_blank" href="{{ URL::to('/download-documents') }}"
                                class="btn btn-info blink">Download Documents</a>
                        @endif
                        <a class="btn btn-success" href="{{URL::to('/results-checker')}}">Check Exam Results</a>
                    </div>
                    <div class="col-12 mt-5 ql-container">
                        <div style="width: 100%">
                            <img src="{{ $document->letter_head }}" alt="letter head" width="100%" />
                        </div>
                        <hr />
                        <div class="ql-editor" style="margin-top: 20px; text-align: justify; text-justify: inter-word">
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
                        <div class="col-12 mt-3">
                            <p><strong>({{ strtoupper($school->signatory_position) }})</strong></p>
                        </div>

                    </div>


                    @if (!empty($letter->acceptance))
                        <div class="col-12 mt-5 ql-container page-break">

                            <div class="ql-editor"
                                style="margin-top: 20px; text-align: justify; text-justify: inter-word"
                                class="page-break">
                                <?php
                                $fee = !empty($fees) ? $fees->amount : '';
                                $placeholders = ['[app_number]', '[hall]', '[programme]', '[phone]', '[date]', '[fee]', '[name]', '[academic_year]'];
                                $values = [$student->application_number, strtoupper($student->hall), strtoupper($programme), $student->phone, date('F jS, Y', strtotime($student->created_at)), $fee, $student->other_names . ' ' . $student->surname, $student->academic_year];

                                $body = str_replace($placeholders, $values, $letter->acceptance);
                                ?>
                                {!! $body !!}
                            </div>
                        </div>
                    @endif

                </div>
            @endif
        @endif
    </div>
@endsection
