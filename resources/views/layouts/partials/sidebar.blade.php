<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <!--begin::Logo image-->
        <a href="{{ route('admin.dashboard') }}">
            <img alt="Logo" src="{{ asset('assets/media/logos/default-dark.svg') }}" class="h-25px app-sidebar-logo-default" />
            <img alt="Logo" src="{{ asset('assets/media/logos/default-small.svg') }}" class="h-20px app-sidebar-logo-minimize" />
        </a>
        <!--end::Logo image-->
        <!--begin::Sidebar toggle-->
        <div id="kt_app_sidebar_toggle" class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate" data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body" data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-duotone ki-black-left-line fs-3 rotate-180">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
        <!--end::Sidebar toggle-->
    </div>
    <!--end::Logo-->

    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <!--begin::Scroll wrapper-->
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer" data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
                <!--begin::Menu-->
                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">

                    <!--begin:Menu item - Dashboard-->
                    <div class="menu-item">
                        <!--begin:Menu link-->
                        <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-element-11 fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </span>
                            <span class="menu-title">لوحة التحكم</span>
                        </a>
                        <!--end:Menu link-->
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Users Management-->
                    @php
                        $usersActive = request()->routeIs('users.*') || request()->routeIs('users.toggle-status');
                    @endphp
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $usersActive ? 'here show' : '' }}">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-user fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">إدارة المستخدمين</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <!--end::Menu link-->
                        <!--begin::Menu sub-->
                        <div class="menu-sub menu-sub-accordion {{ $usersActive ? 'show' : '' }}">
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">قائمة المستخدمين</span>
                                </a>
                            </div>

                        </div>
                        <!--end::Menu sub-->
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Pharmacies Management-->
                    @php
                        $pharmaciesActive = request()->routeIs('pharmacies.*') ||
                                            request()->routeIs('pharmacies.pending') ||
                                            request()->routeIs('pharmacies.approve') ||
                                            request()->routeIs('pharmacies.reject');
                    @endphp
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $pharmaciesActive ? 'here show' : '' }}">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-hospital fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">إدارة الصيدليات</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <!--end::Menu link-->
                        <!--begin::Menu sub-->
                        <div class="menu-sub menu-sub-accordion {{ $pharmaciesActive ? 'show' : '' }}">
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('pharmacies.index') ? 'active' : '' }}" href="{{ route('pharmacies.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">قائمة الصيدليات</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('pharmacies.pending') ? 'active' : '' }}" href="{{ route('pharmacies.pending') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">طلبات التسجيل</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('pharmacies.create') ? 'active' : '' }}" href="{{ route('pharmacies.create') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">إضافة صيدلية</span>
                                </a>
                            </div>
                        </div>
                        <!--end::Menu sub-->
                    </div>
                    <!--end::Menu item-->

                    <!--begin:Menu item - Products (Medicines) Management-->
                    @php
                        $medicinesActive = request()->routeIs('Medicines.*');
                    @endphp
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $medicinesActive ? 'here show' : '' }}">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-medicine fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">إدارة الأدوية</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <!--end::Menu link-->
                        <!--begin::Menu sub-->
                        <div class="menu-sub menu-sub-accordion {{ $medicinesActive ? 'show' : '' }}">
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('Medicines.index') ? 'active' : '' }}" href="{{ route('Medicines.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">قائمة الأدوية</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('Medicines.create') ? 'active' : '' }}" href="{{ route('Medicines.create') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">إضافة دواء</span>
                                </a>
                            </div>
                        </div>
                        <!--end::Menu sub-->
                    </div>
                    <!--end::Menu item-->

                    <!--begin:Menu item - Categories Management-->
                    @php
                        $categoriesActive = request()->routeIs('categories.*');
                    @endphp
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $categoriesActive ? 'here show' : '' }}">
                        <!--begin::Menu link-->
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-category fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">إدارة التصنيفات</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <!--end::Menu link-->
                        <!--begin::Menu sub-->
                        <div class="menu-sub menu-sub-accordion {{ $categoriesActive ? 'show' : '' }}">
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('categories.index') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">قائمة التصنيفات</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('categories.create') ? 'active' : '' }}" href="{{ route('categories.create') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">إضافة تصنيف</span>
                                </a>
                            </div>
                        </div>
                        <!--end::Menu sub-->
                    </div>
                    <!--end::Menu item-->

                    <!--begin:Menu item - Orders Management-->
{{--                    @php--}}
{{--                        $ordersActive = request()->routeIs('orders.*');--}}
{{--                    @endphp--}}
{{--                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ $ordersActive ? 'here show' : '' }}">--}}
{{--                        <!--begin::Menu link-->--}}
{{--                        <span class="menu-link">--}}
{{--                            <span class="menu-icon">--}}
{{--                                <i class="ki-duotone ki-basket fs-2">--}}
{{--                                    <span class="path1"></span>--}}
{{--                                    <span class="path2"></span>--}}
{{--                                    <span class="path3"></span>--}}
{{--                                    <span class="path4"></span>--}}
{{--                                </i>--}}
{{--                            </span>--}}
{{--                            <span class="menu-title">إدارة الطلبات</span>--}}
{{--                            <span class="menu-arrow"></span>--}}
{{--                        </span>--}}
{{--                        <!--end::Menu link-->--}}
{{--                        <!--begin::Menu sub-->--}}
{{--                        <div class="menu-sub menu-sub-accordion {{ $ordersActive ? 'show' : '' }}">--}}
{{--                            <div class="menu-item">--}}
{{--                                <a class="menu-link {{ request()->routeIs('orders.index') ? 'active' : '' }}" href="{{ route('orders.index') }}">--}}
{{--                                    <span class="menu-bullet">--}}
{{--                                        <span class="bullet bullet-dot"></span>--}}
{{--                                    </span>--}}
{{--                                    <span class="menu-title">جميع الطلبات</span>--}}
{{--                                </a>--}}
{{--                            </div>--}}

{{--                            <div class="menu-item">--}}
{{--                                <a class="menu-link" href="#">--}}
{{--                                    <span class="menu-bullet">--}}
{{--                                        <span class="bullet bullet-dot"></span>--}}
{{--                                    </span>--}}
{{--                                    <span class="menu-title">الطلبات المعلقة</span>--}}
{{--                                </a>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <!--end::Menu sub-->--}}
{{--                    </div>--}}
                    <!--end::Menu item-->

                </div>
                <!--end::Menu-->
            </div>
            <!--end::Scroll wrapper-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->

    <!--begin::Footer-->
    <div class="app-sidebar-footer flex-column-auto pt-2 pb-6 px-6" id="kt_app_sidebar_footer">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-flex flex-center btn-custom btn-primary overflow-hidden text-nowrap px-0 h-40px w-100" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss-="click" title="نظام الصيدلية الإلكترونية">
            <span class="btn-label">Online Pharmacy System</span>
            <i class="ki-duotone ki-document btn-icon fs-2 m-0">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </a>
    </div>
    <!--end::Footer-->
</div>
