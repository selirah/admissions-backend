@extends ('layouts.layout')
@section('content')

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6 offset-3">
                <table class="table table-borderless" style="margin-top: 100px; margin-bottom: 100px">
                    @for ($i = 0; $i < count($documents); $i++)
                        <tr>
                            <td>{{ $documents[$i] }}</td>
                            <td><a href="{{ url('uploads/docs/' . $documents[$i]) }}" class="btn btn-outline-info"><i
                                        class="fa fa-download"> Download</i></a></td>
                        </tr>
                    @endfor
                </table>
            </div>
        </div>
    </div>

@endsection
