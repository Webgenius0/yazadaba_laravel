@extends('backend.app')

@section('title', 'Withdraw Complete List')

@push('style')
    <style>
        .custom-confirm-button {
            background-color: #04AA6D !important;
            /* Green */
            color: white !important;
        }

        .btn-smaller {
            padding: 2px 8px;
            font-size: 0.8rem;
            height: 25px;
        }

        .custom-cancel-button {
            background-color: #f72213 !important;
            /* Red */
            color: white !important;
        }

        /* Optional: Change button on hover */
        .custom-confirm-button:hover {
            background-color: #ff4e02;
            /* Darker green */
        }

        .custom-cancel-button:hover {
            background-color: #f51808;
            /* Darker red */
        }
    </style>
    <link rel="stylesheet" href="{{ asset('backend/vendors/datatable/css/datatables.min.css') }}">
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Withdraw Complete List</h4>
                        <div class="table-responsive mt-4 p-4">
                            <table class="table table-hover" id="data-table">
                                <thead>
                                    <tr>
                                        <th>SI</th>
                                        <th>Name</th>
                                        <th>Request Amount</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('script')
    {{-- Datatable --}}
    <script src="{{ asset('backend/vendors/datatable/js/datatables.min.js') }}"></script>
    {{-- SweetAlert --}}
    <script src="{{ asset('backend/vendors/sweetalert/sweetalert2@11.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/38.0.0/classic/ckeditor.js"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                }
            });

            if (!$.fn.DataTable.isDataTable('#data-table')) {
                $('#data-table').DataTable({
                    order: [],
                    lengthMenu: [
                        [10, 25, 50, 100, 200, 500, -1],
                        ["10", "25", "50", "100", "200", "500", "All"]
                    ],
                    pageLength: 10,
                    processing: true,
                    responsive: true,
                    serverSide: true,
                    language: {
                        processing: `<div class="text-center">
                            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                          </div>
                            </div>`,
                        lengthMenu: '_MENU_',
                        search: '',
                        searchPlaceholder: 'Search..'
                    },
                    ajax: {
                        url: "{{ route('admin.withdraw.request.index') }}",
                        type: "get",
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'user_name', name: 'user_name', orderable: true, searchable: true },
                        { data: 'amount', name: 'amount', orderable: true, searchable: true },
                        { data: 'status', name: 'status', orderable: true, searchable: true },
                        { data: 'created_at', name: 'created_at', orderable: true, searchable: true },
                        { data: 'action', name: 'action', orderable: false, searchable: false },
                    ],
                });
            }
        });
        // SweetAlert Delete confirm
        const deleteAlert = (id) => {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteAuction(id);
                }
            });
        }

        // Deleting an auction
        const deleteAuction = (id) => {
            try {
                let url = '{{ route('admin.withdraw.request.destroy', ':id') }}';
                let csrfToken = `{{ csrf_token() }}`;
                $.ajax({
                    type: "DELETE",
                    url: url.replace(':id', id),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: (response) => {
                        $('#data-table').DataTable().ajax.reload();

                        if (response.success === true) {
                            Swal.fire({
                                title: "Deleted!",
                                text: "Data has been deleted.",
                                icon: "success"
                            });
                        } else if (response.errors) {
                            console.log(response.errors[0]);
                            toastr.error(response.errors[0]);
                        } else {
                            toastr.success(response.message);
                        }
                    },
                    error: (error) => {
                        console.log(error.message);
                        toastr.error('Something went wrong.');
                    }
                });
            } catch (e) {
                console.log(e);
            }
        }
        //reject withdraw request
        function openRejectModal(event, id, userId) {
            Swal.fire({
                title: "Provide a Reject Reason",
                html: `<textarea id="reject-reason" class="swal2-input"></textarea>`,
                inputAttributes: {
                    autocapitalize: "off"
                },
                showCancelButton: true,
                confirmButtonText: "Submit",
                showLoaderOnConfirm: true,
                didOpen: () => {
                    ClassicEditor.create(document.querySelector('#reject-reason'))
                        .then(editor => {
                            // Save the editor instance to the textarea to access it later
                            window.editor = editor;
                        });
                },
                preConfirm: async () => {
                    try {
                        // Get the data from CKEditor
                        const rejectReason = window.editor.getData().trim();
                        if (!rejectReason) {
                            Swal.showValidationMessage('Reject Reason is required!');
                            return false;
                        }

                        // Submit the rejection reason to the backend
                        const response = await fetch(`/withdraw-requests/${id}/${userId}/reject`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                rejection_reason: rejectReason
                            })
                        });

                        if (!response.ok) {
                            const errorData = await response.json();
                            return Swal.showValidationMessage(`Error: ${errorData.message}`);
                        }

                        return response.json();
                    } catch (error) {
                        Swal.showValidationMessage(`Request failed: ${error}`);
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Rejection Submitted', 'The rejection reason has been submitted successfully.', 'success')
                        .then(() => {
                            // Reload the DataTable here after clicking "OK" button
                            $('#data-table').DataTable().ajax.reload();
                        });
                }
            });
        }


        function showStatusChangeAlert(event, id, status) {
            event.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to update the status to ' + status + '?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.isConfirmed) {
                    statusChange(id, status);
                }
            });
        }

        // Status Change
        function statusChange(id, status) {
            var url = '{{ route('admin.withdraw.request.status', ':id') }}';
            url = url.replace(':id', id);

            $.ajax({
                type: "POST",
                url: url,
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                },
                success: function(resp) {
                    $('#data-table').DataTable().ajax.reload();

                    if (resp.success === true) {
                        toastr.success(resp.message);
                    } else {
                        toastr.error(resp.message);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Something went wrong. Please try again.');
                }
            });
        }

    </script>
@endpush


