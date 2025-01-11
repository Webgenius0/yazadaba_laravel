@extends('backend.app')

@section('title', 'Profile Setting')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Terms And Conditions</h4>
                     
                        <div class="mt-4">
                            <form id="termsForm" class="forms-sample" method="POST" action="{{route('admin.terms-and-condition.updatePrivecyPolicy')}}" >
                                @csrf
                                <div class="form-group row mb-3">
                                    <div class="col-12">
                                        <label class="form-lable">Privecy</label>
                                  
                                            <textarea class="form-control form-control-solid" name="privacy_policy" id="privacy_policy" value="">{{  $privecyPolicy->privacy_policy ?? 'privacy'  }}</textarea>

                                        @error('privacy')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row mb-3">
                                    <div class="col-12">
                                        <label class="form-lable">Policy</label>
                                        
                                            <textarea class="form-control form-control-solid" name="policy" id="policy" value="">{{  $privecyPolicy->policy ?? 'policy'  }}</textarea>

                                        @error('policy')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary me-2">Submit</button>
                            </form>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
@endsection

@push('script')
     <script src="https://cdn.ckeditor.com/ckeditor5/38.0.0/classic/ckeditor.js"></script>
<script>
   
    ClassicEditor
            .create(document.querySelector('#terms'))
            .catch(error => {
                console.error(error);
            });

 ClassicEditor
            .create(document.querySelector('#conditions'))
            .catch(error => {
                console.error(error);
            });

</script>
@endpush