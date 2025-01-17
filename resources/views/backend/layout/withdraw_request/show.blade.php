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
                        <div class="card-body pt-12">
                            <div class="user-avatar-section">
                                <div class=" d-flex align-items-center flex-column">
                                    <img class="img-fluid rounded mb-4" src="{{asset($user->avatar ??'backend/assets/img/avatars/1.png')}}" height="120" width="120" alt="User avatar">
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
                <div class="col-xl-8 col-lg-7 order-0 order-md-1">
                  
                    <div class="card mb-6 mt-3">
                        <h5 class="card-header d-flex justify-content-between align-items-center">
                            <span>Account Information</span>
                        </h5>
                        <div class="card-body pt-1">
                            <ul class="timeline mb-0">
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-primary"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-3">
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
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Modal Structure -->
                <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Reject Reason</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group mb-3">
                                    <label class="form-lable required mt-1">Reject Reason</label>
                                    <textarea id="description" name="reject_reason" cols="20" rows="10"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <!-- Close Button inside Modal Footer -->
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.2.0/classic/ckeditor.js"></script>
    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#description'))
            .catch(error => {
                console.error(error);
            });
    </script>
    <!-- JavaScript to trigger modal -->
    <script>
        // When the button is clicked, show the modal
        document.getElementById('openModalBtn').addEventListener('click', function () {
            var myModal = new bootstrap.Modal(document.getElementById('activityModal'));
            myModal.show();
        });
    </script>
@endpush
