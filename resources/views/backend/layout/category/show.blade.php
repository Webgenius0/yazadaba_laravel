@extends('backend.app')

@section('title', 'View Course')

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
    <style>
        .ck-editor__editable[role="textbox"] {
            min-height: 150px;
        }

        .course-modules {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: flex-start;
        }

        .module-card {
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 100%;
            padding: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .module-card:hover {
            transform: translateY(-5px);
            color: #1f2937;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .module-header {
            border-bottom: 1px solid #ddd;
            margin-bottom: 10px;
            padding-bottom: 8px;
        }

        .module-header h3 {
            font-size: 18px;
            color: #333;
            margin: 0;
        }

        .module-body p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }

        .module-body strong {
            color: #333;
        }

        .course-modules {
            display: flex;
            flex-direction: row;
            max-height: 500px;
            overflow-y: auto;

        }


    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">View Courses</h4>
                        <hr>
                        <div class="mt-4">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Categories</h5>
                                            <hr>
                                            <h5 class="card-title"><strong>Category Name
                                                    :</strong> {{ $data->category->name ?? '' }}</h5>
                                            <h5><strong>Status:</strong>
                                                <span class="btn btn-sm
        {{$data->category->status === 'active' ? 'btn-primary' : ($data->category->status === 'inactive' ?
        'btn-warning' : 'btn-secondary')}}"
                                                      style="padding: 2px 10px;">
        {{$data->category->status ?? 'inactive'}}
    </span>
                                            </h5>
                                            <p>
                                                <img
                                                    src="{{ asset($data->category->icon ?? 'backend/images/logo.png') }}"
                                                    alt="Category Icon"
                                                    style="width: 100%; height: 200px;">
                                            </p>

                                        </div>
                                    </div>
                                    <div class="card mt-5">
                                        <div class="card-body">
                                            <h5 class="card-title">Grade Levels</h5>
                                            <hr>
                                            <h5 class="card-title"><strong>Grade
                                                    Level:</strong> {{ $data->gradeLevel->name ?? '' }}</h5>
                                            <h5><strong>Status:</strong>
                                                <span class="btn btn-sm
        {{$data->gradeLevel->status === 'active' ? 'btn-primary' : ($data->gradeLevel->status === 'inactive' ?
        'btn-warning' : 'btn-secondary')}}"
                                                      style="padding: 2px 10px;">
        {{$data->status ?? 'inactive'}}
    </span>
                                            </h5>

                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Course Details</h5>
                                            <hr>
                                            <p class="card-text"><strong>Course Name:</strong> {{$data->name ?? 'N/A'}}
                                            </p>
                                            <p class="card-text"><strong>Course
                                                    Details:</strong> {!! $data->description ?? 'N/A' !!}</p>
                                            <p class="card-text"><strong>Course Total Duration:</strong>
                                                {{ is_numeric($data->course_duration) ? \Carbon\Carbon::createFromTimestamp($data->course_duration * 60)->format('H:i:s') : 'N/A' }}
                                            </p>
                                            <p class="card-text"><strong>Created At:</strong>
                                                {{ \Carbon\Carbon::parse($data->created_at)->format('D F Y \a\t h:i A') ?? 'N/A' }}
                                            </p>
                                            <p class="card-text"><strong>Updated At:</strong>
                                                {{ \Carbon\Carbon::parse($data->updated_at)->format('D F Y \a\t h:i A') ?? 'N/A' }}
                                            </p>
                                            <h5><strong>Status:</strong>
                                                <span class="btn btn-sm
        {{$data->status === 'active' ? 'btn-primary' : ($data->status === 'inactive' ? 'btn-warning' : 'btn-secondary')}}"
                                                      style="padding: 2px 10px;">
        {{$data->status ?? 'inactive'}}
    </span>
                                            </h5>

                                            <p><img src="{{asset( $data->cover_image ?? 'backend/images/logo.png')}}"
                                                    alt="Course Image" style="width: 100%; height: 200px;"></p>

                                        </div>
                                    </div>
                                    <div class="card mt-5">
                                        <div class="card-body">
                                            <h5 class="card-title">Module Details Details</h5>
                                            <hr>
                                            <div class="course-modules">
                                                @if($data->courseModules && $data->courseModules->count() > 0)
                                                    @foreach ($data->courseModules as $module)
                                                        <div class="module-card">
                                                            <div class="module-header">
                                                                <h3>Section {{ $loop->iteration }}
                                                                    - {{ $module->title }}</h3>
                                                            </div>
                                                            <div class="module-body">
                                                                <p>
                                                                    <strong>Duration:</strong> {{ $module->duration ?? '00:00' }}
                                                                    min</p>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p>No modules available for this course.</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
