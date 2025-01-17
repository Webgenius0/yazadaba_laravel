@php
    $setting = \App\Models\SystemSetting::first();
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="" class="app-brand-link">
            @if ($setting && $setting->logo)
                <img src="{{ asset($setting->logo) }}" style="height: 95px;width: 176px;" alt="Logo">
            @else
                <img src="{{ asset('backend/images/logo.png') }}" style="height: 95px;width: 176px;" alt="Default Logo">
            @endif
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    {{-- <div class="menu-inner-shadow"></div> --}}
    <li class="menu-header small text-uppercase"><span class="menu-header-text">Dashboard</span></li>


    <ul class="menu-inner py-1">

        <li class="menu-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('dashboard') }}">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>
        {{-- ..................................................... --}}


        <!-- Category -->
        <li
            class="menu-item {{ Request::routeIs('admin.category.*') || Request::routeIs('admin.category') ? 'active' : '' }}">
            <a href="{{ route('admin.category.index') }}" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-badge-check'></i>
                <div data-i18n="Layouts">Categories</div>
            </a>
        </li>
        <!-- Category End  -->

        <!-- Grade Level start  -->
        <li
            class="menu-item {{ Request::routeIs('admin.grade-level.*') || Request::routeIs('admin.grade-level') ? 'active' : '' }}">
            <a href="{{ route('admin.grade-level.index') }}" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-badge-check'></i>
                <div data-i18n="Layouts">Grade Levels</div>
            </a>
        </li>
        <!-- Grade Level end -->

        <!-- Course start -->
        <li
            class="menu-item {{ Request::routeIs('admin.course.*') || Request::routeIs('admin.course') ? 'active' : '' }}">
            <a href="{{ route('admin.course.index') }}" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-badge-check'></i>
                <div data-i18n="Layouts">Courses</div>
            </a>
        </li>
        <!-- Course end -->
        <!-- Withdraw Request Start -->
        <li
            class="menu-item {{ Request::routeIs('admin.withdraw.request*') ||
            Request::routeIs('admin.withdraw.complete*') ||
            Request::routeIs('admin.withdraw.reject*')
                ? 'active open'
                : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Layouts">Withdraw</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ Request::routeIs('admin.withdraw.request.index') ? 'active' : '' }}">
                    <a class="menu-link" href="{{ route('admin.withdraw.request.index') }}">Requests</a>
                </li>
                <li class="menu-item {{ Request::routeIs('admin.withdraw.complete.index') ? 'active' : '' }}">
                    <a class="menu-link" href="{{ route('admin.withdraw.complete.index') }}">Request Complete</a>
                </li>
                <li class="menu-item {{ Request::routeIs('admin.withdraw.reject.index') ? 'active' : '' }}">
                    <a class="menu-link" href="{{ route('admin.withdraw.reject.index') }}">Reject Lists</a>
                </li>
            </ul>
        </li>
        <!-- Withdraw Request End  -->

        <!-- Settings -->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Settings</span></li>
        <!-- Layouts -->
        <li
            class="menu-item {{ Request::routeIs('system.setting') || Request::routeIs('system.mail.index') || Request::routeIs('stripe.index') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Layouts">Settings</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ Request::routeIs('system.setting') ? 'active' : '' }}"><a class="menu-link"
                        href="{{ route('system.setting') }}">System
                        Settings</a></li>

                <li class="menu-item {{ Request::routeIs('system.mail.index') ? 'active' : '' }}"><a class="menu-link"
                        href="{{ route('system.mail.index') }}">Mail
                        Setting</a></li>

            </ul>
        </li>


        <!--terms&&condition-->
        <li class="menu-item {{ Request::routeIs('admin.terms-and-condition.index') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('admin.terms-and-condition.index') }}">
                <i class="menu-icon tf-icons bx bx-file"></i>


                <div data-i18n="Support">Terms & Condition</div>
            </a>
        </li>


        <!--privacyPolicy-->
        <li class="menu-item {{Request::routeIs('admin.terms-and-condition.privacyPolicy') ? 'active' : ''}}">
            <a class="menu-link" href="{{ route('admin.terms-and-condition.privacyPolicy') }}">
                <i class="menu-icon tf-icons bx bx-lock"></i>

                <div data-i18n="Support">Privacy Policy</div>
            </a>
        </li>


        {{-- prifile seatting --}}
        <li class="menu-item {{ Request::routeIs('profile.setting') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('profile.setting') }}">
                <i class="menu-icon tf-icons bx bxs-user-account"></i>
                <div data-i18n="Support">Profile Setting</div>
            </a>
        </li>
    </ul>
</aside>
