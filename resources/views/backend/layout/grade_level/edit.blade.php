@extends('backend.app')

@section('title', 'Edit Grade Level')

@push('style')
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Edit Grade Level</h4>
                        <form id="my-form" class="forms-sample" action="{{route('admin.grade-level.update',
                        $grade_level->id)}}"
                              method="POST"
                              enctype="multipart/form-data">
                            @method('PUT')
                            @csrf
                            {{-- name --}}
                            <div class="form-group mb-3">
                                <label class="form-lable required">Grade Level Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name"  value="{{ old('name') ?? $grade_level->name ?? '' }}"
                                       placeholder="Name Here...">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
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

@endpush
