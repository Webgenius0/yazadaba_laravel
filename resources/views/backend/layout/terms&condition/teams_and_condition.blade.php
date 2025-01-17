@extends('backend.app')

<<<<<<<< HEAD:resources/views/backend/layout/terms&condition/teams_and_condition.blade.php
@section('title', 'Terms and Conditions')
========
@section('title', 'Terms And Conditions')
>>>>>>>> caedb0cbee2cf1253ea63baea4b886ef4d89d760:resources/views/backend/layout/terms&condition/termsandCondition.blade.php

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
<<<<<<<< HEAD:resources/views/backend/layout/terms&condition/teams_and_condition.blade.php
                        <h4 class="card-title">Terms and Conditions</h4>
========
                        <h4 class="card-title">Terms And Conditions</h4>
                 
>>>>>>>> caedb0cbee2cf1253ea63baea4b886ef4d89d760:resources/views/backend/layout/terms&condition/termsandCondition.blade.php
                        <div class="mt-4">
                            <form id="termsForm" class="forms-sample" method="POST" action="{{route('admin.terms-and-condition.update')}}">
                                @csrf
                                <div class="form-group row mb-3">
                                    <div class="col-12">
                                        <label class="form-lable">Terms</label>
<<<<<<<< HEAD:resources/views/backend/layout/terms&condition/teams_and_condition.blade.php
                                            <textarea class="form-control form-control-solid" name="terms" id="terms" value="">{{  $termsAndCondition->terms ?? ''  }}</textarea>
========
                                       
                                            <textarea class="form-control form-control-solid" name="terms" id="terms" value="">{{  $termsAndCondition->terms ?? 'terms'  }}</textarea>
>>>>>>>> caedb0cbee2cf1253ea63baea4b886ef4d89d760:resources/views/backend/layout/terms&condition/termsandCondition.blade.php

                                        @error('terms')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row mb-3">
                                    <div class="col-12">
                                        <label class="form-lable">Conditions</label>
                                            <textarea class="form-control form-control-solid" name="conditions" id="conditions" value="">{{  $termsAndCondition->conditions ?? ''  }}</textarea>
                                        @error('conditions')
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
