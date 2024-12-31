@extends('backend.app')

@section('title', 'Verifications View')

@push('style')
    <style>
        .ck-editor__editable[role="textbox"] {
            min-height: 150px;
        }
    </style>
@endpush

@section('content')

    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">View Verifications</h4>
                        <div class="card mb-4">
                            <!-- Account -->
                            <div class="card-body">
                                <div class="d-flex align-items-start align-items-sm-center gap-4">
                                    <img src="{{asset('backend/assets/img/avatars/1.png')}}" alt="user-avatar" class="d-block rounded"
                                        height="100" width="100" id="uploadedAvatar" />
                                </div>
                            </div>
                            <hr class="my-0" />
                            <div class="card-body">
                                <form id="formAccountSettings" method="POST">
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label for="firstName" class="form-label">First Name</label>
                                            <input class="form-control" type="text" id="firstName" name="firstName"
                                                value="{{ $data->first_name ?? '' }}" disabled/>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="lastName" class="form-label">Last Name</label>
                                            <input class="form-control" type="text" name="lastName" id="lastName"
                                                value="{{ $data->last_name ?? '' }}" disabled/>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="email" class="form-label">Address</label>
                                            <input class="form-control" type="text" id="email" name="email"
                                                value="{{ $data->address ?? '' }}" placeholder="john.doe@example.com" disabled/>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="organization" class="form-label">City</label>
                                            <input type="text" class="form-control" id="organization" name="organization"
                                                value="{{ $data->city ?? '' }}" disabled/>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label" for="phoneNumber">State</label>
                                            <div class="input-group input-group-merge">
                                                <input type="text" id="phoneNumber" name="phoneNumber"
                                                    class="form-control" placeholder="202 555 0111"
                                                    value="{{ $data->state ?? '' }}" disabled/>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="zipCode" class="form-label">Zip Code</label>
                                            <input type="text" class="form-control" id="zipCode" name="zipCode"
                                                placeholder="231465" maxlength="6" value="{{ $data->zipcode ?? '' }}" disabled/>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label" for="country">Store Ny</label>
                                            <select id="country" class="select2 form-select">
                                                <option value="United Kingdom">United Kingdom</option>
                                                <option value="United States">United States</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label" for="country">Store</label>
                                            <select id="country" class="select2 form-select">
                                                <option value="United Kingdom">United Kingdom</option>
                                                <option value="United States">United States</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 col-md-6">
                                            <label class="form-label" for="country">Business Web Url</label>
                                            <input type="text" class="form-control" id="business_url"
                                                name="business_url" value="{{ $data->business_url ?? '' }}" disabled/>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label" for="country">Business Address</label>
                                            <input type="text" class="form-control" id="business_address"
                                                name="business_address" value="{{ $data->business_address ?? '' }}" disabled/>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label" for="country">Business City</label>
                                            <input type="text" class="form-control" id="business_city"
                                                name="business_city" value="{{ $data->business_city ?? '' }}" disabled/>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label" for="country">Business State</label>
                                            <input type="text" class="form-control" id="business_state"
                                                name="business_state" value="{{ $data->business_state ?? '' }}" disabled/>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label" for="country">Business Zipcode</label>
                                            <input type="text" class="form-control" id="business_zipcode"
                                                name="business_zipcode" value="{{ $data->business_zipcode ?? '' }}" disabled/>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="submit" class="btn btn-success me-2">Accept</button>
                                        <button type="reset" class="btn btn-outline-danger">Cancel</button>
                                    </div>
                                </form>
                            </div>
                            <!-- /Account -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.2.0/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            ClassicEditor
                .create(document.querySelector('#content'), {
                    height: '500px'
                })
                .catch(error => {
                    console.error(error);
                });
        });
    </script>
@endpush
