@extends('backend.app')

@section('title', 'View Course')

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
    <style>

    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <!-- User Sidebar -->
                <div class="col-xl-4 col-lg-5 order-1 order-md-0">
                    <!-- User Card -->
                    <div class="card mb-6">
                        <div class="card mb-6">
                            <div class="card-body pt-12">
                                <div class="user-avatar-section">
                                    <div class=" d-flex align-items-center flex-column">
                                        <img class="img-fluid rounded mb-4" src="{{asset($user->avatar ?? 'backend/assets/img/avatars/1.png')}}" height="120" width="120" alt="User avatar">
                                        <div class="user-info text-center">
                                            <h5>{{$user->name ?? 'N/A'}}</h5>
                                            <span class="badge bg-label-secondary">{{$user->role ?? 'N/A'}}</span>
                                        </div>
                                    </div>
                                </div>
                                <h5 class="pb-4 border-bottom mb-4">Details</h5>
                                <div class="info-container">
                                    <ul class="list-unstyled mb-6">
                                        <li class="mb-2">
                                            <span class="h6">Username:</span>
                                            <span>{{$user->name ?? 'N/A'}}</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="h6">Email:</span>
                                            <span>{{$user->email ?? 'N/A'}}</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="h6">Role:</span>
                                            <span>{{$user->role ?? 'N/A'}}</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="h6">Phone:</span>
                                            <span>{{$user->phone ?? 'N/A'}}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-lg-7 order-0 order-md-1">
                    <!-- Activity Timeline -->
                    <div class="card mb-6">
                        <h5 class="card-header">Account Information</h5>
                        <div class="card-body pt-1">
                            <ul class="list-unstyled mb-6">
                                <li class="mb-2">
                                    <span class="h6">Request Amount:</span>
                                    <span>{{$bank_info->amount ?? 'N/A'}}</span>
                                </li>
                                <li class="mb-2">
                                    <span class="h6">Bank Information:</span>
                                    <span>{{$bank_info->bank_info ?? 'N/A'}}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Activity Timeline -->
                </div>
            </div>

        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.2.0/classic/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script type="text/javascript" src="https://jeremyfagis.github.io/dropify/dist/js/dropify.min.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#description'))
            .catch(error => {
                console.error(error);
            });
    </script>
@endpush
