@extends ('layouts.layout')
@section('content')
    @if ($message = Session::get('success'))
        <div class="container-fluid mt-4">
            <div class="row">
                <div class="col-8">
                    <div class="container text-center">
                        <h1 class="display-4" style="color: #00C853">Nice!</h1>
                        <p class="lead alert alert-success">You have successfully uploaded receipt. Please wait for approval
                            from the school via
                            SMS</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="container-fluid mt-4">
        <form action="{{ route('upload-receipt.post') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-8">
                    <div class="row flex-row justify-content-center">
                        <div class="col-12 mb-4">
                            <p>File must be in .jpg, .jpeg, .png, or .pdf format</p>
                            <input type="file" name="image" class="form-control" accept=".png, .jpeg, .jpg, .pdf">
                        </div>
                    </div>
                    <div class="row flex-row justify-content-center mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">Upload</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
