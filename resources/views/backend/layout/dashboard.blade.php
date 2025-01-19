@extends('backend.app')

@section('title', 'Dashboard')

@section('content')
    @php
       use App\Models\CourseEnroll;
       use App\Models\User;
        $userCount = User::count();
      $totalAmount = CourseEnroll::sum('amount');
$seventyFivePercent = $totalAmount * 0.75;
 @endphp
<div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">

      <div class="row">
        <div class="col-lg-8 mb-4 order-0">
          <div class="card">
            <div class="d-flex align-items-end row">
              <div class="col-sm-7">
                <div class="card-body">
                  <h5 class="card-title text-primary">Hello, {{ Auth::user()->name ?? '' }} {{ Auth::user()->last_name ?? '' }}! 🎉</h5>
                  <p class="mb-4">
                    Hope you are having a nice day!
                  </p>

                </div>
              </div>
              <div class="col-sm-5 text-center text-sm-left">
                <div class="card-body pb-0 px-0 px-md-4">
                  <img
                    src="{{ asset('backend/assets/img/illustrations/man-with-laptop-light.png') }}"
                    height="140"
                    alt="View Badge User"
                    data-app-dark-img="illustrations/man-with-laptop-dark.png"
                    data-app-light-img="illustrations/man-with-laptop-light.png"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-4 order-1">
          <div class="row">
            <div class="col-lg-6 col-md-12 col-6 mb-4">
              <div class="card">
                <div class="card-body">
                  <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                      <img
                        src="{{ asset('backend/assets/img/icons/unicons/users.png') }}"
                        alt="user"
                        class="rounded"
                      />
                    </div>

                  </div>
                  <span class="fw-semibold d-block mb-1">Total User</span>
                  <h3 class="card-title mb-2">{{ $userCount ?? '0' }}</h3>
                </div>
              </div>
            </div>
            <div class="col-lg-6 col-md-12 col-6 mb-4">
              <div class="card">
                <div class="card-body">
                  <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <svg class="rounded" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                            <path d="M12 17v5"></path>
                            <path d="M12 12l10 5-10 5-10-5 10-5z"></path>
                        </svg>
                    </div>
                  </div>
                  <span class="fw-semibold d-block mb-1">Total Amount</span>
                  <h3 class="card-title mb-2">{{$seventyFivePercent ?? '0.0'}}</h3>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
   </div>
    <!-- / Content -->

    <div class="content-backdrop fade"></div>
  </div>
@endsection

@push('script')

@endpush
