@extends('backend.app')

@section('title', 'Edit Category')

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
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
                        <h4 class="card-title">Edit Category</h4>
                        <form id="my-form" class="forms-sample" action="{{route('admin.category.update', $category->id)}}"
                              method="POST"
                              enctype="multipart/form-data">
                            @method('PUT')
                            @csrf
                            {{-- name --}}
                            <div class="form-group mb-3">
                                <label class="form-lable required">Category Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name"  value="{{ old('name') ?? $category->name ?? '' }}"
                                       placeholder="Name Here...">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label required">Category Icon</label>
                                <input
                                    type="file"
                                    class="dropify form-control @error('icon') is-invalid @enderror"
                                    name="icon" data-default-file="{{ old('icon') ?? asset($category->icon ?? '') }}">
                                @error('icon')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary me-2">Submit</button>
                            <a href="" class="btn btn-danger ">Cancel</a>
                        </form>
                    </div>
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
