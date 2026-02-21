<!DOCTYPE html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="../../assets/" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <!-- Add CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @yield('token')
    <title>@yield('title')</title>
    {{-- <title>Dashboard - {{ config('app.name', 'Laravel') }}</title> --}}

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/branding/favicon.png') }}?v=3" />
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/img/branding/favicon.png') }}?v=3" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/branding/favicon.png') }}?v=3" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/materialdesignicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <!-- Font Awesome for chatbot icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Menu waves for no-customizer fix -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/swiper/swiper.css') }}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/cards-statistics.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/cards-analytics.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />

    <style>
        :root {
            --brand-primary: #1F7A4C;
            --brand-secondary: #2FAE7A;
            --brand-accent: #4CC2B8;
            --brand-text: #1F2933;
            --brand-bg: #F4F7F6;
            --brand-border: #E0E6E4;
        }

        body,
        .layout-page,
        .content-wrapper,
        .bg-footer-theme {
            background: var(--brand-bg) !important;
            color: var(--brand-text);
        }

        a,
        a:hover {
            color: var(--brand-primary);
        }

        .btn-primary {
            background-color: var(--brand-primary) !important;
            border-color: var(--brand-primary) !important;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background-color: var(--brand-secondary) !important;
            border-color: var(--brand-secondary) !important;
        }

        .menu-vertical .menu-item.active>.menu-link {
            background-color: var(--brand-primary) !important;
            color: #fff !important;
        }

        .menu-vertical .menu-item.active>.menu-link i,
        .menu-vertical .menu-item.active>.menu-link div {
            color: #fff !important;
        }

        .menu-vertical .menu-link {
            color: var(--brand-text);
        }

        .form-check-input:checked {
            background-color: var(--brand-primary) !important;
            border-color: var(--brand-primary) !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--brand-accent);
            box-shadow: 0 0 0 .2rem rgba(76, 194, 184, .2);
        }

        .card,
        .dropdown-menu,
        .table {
            border-color: var(--brand-border);
        }

        .table thead th,
        table.dataTable thead th,
        table.dataTable>thead>tr>th,
        .dataTables_wrapper .dataTables_scrollHead table thead th {
            background-color: var(--brand-primary) !important;
            color: #ffffff !important;
            font-weight: 700;
            border-bottom-color: var(--brand-primary) !important;
        }

        table.dataTable thead th.sorting,
        table.dataTable thead th.sorting_asc,
        table.dataTable thead th.sorting_desc {
            background-color: var(--brand-primary) !important;
            color: #ffffff !important;
        }

        .card-datatable .btn-primary,
        table.dataTable .btn-primary,
        .dropdown .btn-primary {
            background-color: var(--brand-primary) !important;
            border: 1px solid var(--brand-primary) !important;
            color: #fff !important;
        }

        .card-datatable .btn-primary:hover,
        table.dataTable .btn-primary:hover,
        .dropdown .btn-primary:hover {
            background-color: var(--brand-secondary) !important;
            border-color: var(--brand-secondary) !important;
            color: #fff !important;
        }

        .card-datatable,
        .dataTables_wrapper,
        .dataTables_wrapper .row,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            background-color: var(--brand-bg) !important;
        }

        table.dataTable,
        .table.dataTable,
        .table-striped>tbody>tr:nth-of-type(odd)>*,
        .table-striped>tbody>tr:nth-of-type(even)>* {
            background-color: #ffffff !important;
            color: var(--brand-text) !important;
        }

        table.dataTable tbody tr td,
        table.dataTable tbody tr th {
            border-color: var(--brand-border) !important;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate .paginate_button,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            color: var(--brand-text) !important;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            background-color: #fff !important;
            border: 1px solid var(--brand-border) !important;
            color: var(--brand-text) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--brand-primary) !important;
            border-color: var(--brand-primary) !important;
            color: #fff !important;
        }

        .dropdown-menu .dropdown-item {
            color: var(--brand-text) !important;
        }

        .dropdown-menu .dropdown-item:hover,
        .dropdown-menu .dropdown-item:focus {
            color: var(--brand-primary) !important;
            background-color: rgba(47, 174, 122, 0.12) !important;
        }

        .module-card-header .card-title {
            color: #fff !important;
            background: transparent !important;
            border: 0 !important;
            border-radius: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
        }

        /* ::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 6px #1F7A4C;
            border-radius: 10px;
            background-color: #1F7A4C;
        }

        ::-webkit-scrollbar {
            width: 8px;
            padding-top: 10px;
            background-color: #1F7A4C;
        }

        ::-webkit-scrollbar-thumb {
            border-radius: 10px;
            -webkit-box-shadow: inset 0 0 6px #1F7A4C;
            background-color: #f5f5f5;
            height: 20px;
        } */

        .app-brand {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .app-brand-link {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px 0;
        }

        .sidebar-logo-wrap {
            width: 220px;
            max-width: 92%;
            min-height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-logo {
            width: 100%;
            max-height: 120px;
            height: auto;
            object-fit: contain;
            object-position: center;
            display: block;
        }

        .red-text {
            color: red;
        }

        /* Global SweetAlert policy: never show deny/no button */
        .swal2-deny {
            display: none !important;
        }

        .parsley-required,
        .parsley-type,
        .parsley-errors-list  {
            color: red !important;
        }

        .parsley-errors-list{
            font-size: 12px !important;
        }

        .table-responsive {
            overflow: unset;
        }

        .dataTables_scrollBody {
            overflow: unset !important;
        }

        div.dataTables_processing>div:last-child>div {
            background: var(--brand-primary);
        }

        .layout-navbar-fixed:not(.layout-menu-collapsed) .layout-content-navbar:not(.layout-without-menu) .layout-navbar,
        .layout-menu-fixed.layout-navbar-fixed:not(.layout-menu-collapsed) .layout-content-navbar:not(.layout-without-menu) .layout-navbar {
            left: 17.63rem !important;
        }

        .layout-navbar-fixed .layout-navbar.navbar-detached {
            width: 84% !important;
        }

        .align {
            text-align: left !important;
        }

        .text-truncate {
            white-space: wrap;
        }

        #layout-navbar {
            -webkit-backdrop-filter: blur(6px) !important;
        }

        @media screen and (max-width: 1440px) {
            .layout-navbar-fixed .layout-navbar.navbar-detached {
                width: 79% !important;
            }
        }

        @media screen and (max-width: 1366px) {
            .layout-navbar-fixed .layout-navbar.navbar-detached {
                width: 78% !important;
            }
        }

        @media screen and (max-width: 1024px) {
            .close-menu {
                position: absolute;
                right: 5px;
                top: 5px;
                background-color: var(--brand-primary);
                color: #fff;
                border-radius: 5px;
            }

            .layout-navbar-fixed:not(.layout-menu-collapsed) .layout-content-navbar:not(.layout-without-menu) .layout-navbar,
            .layout-menu-fixed.layout-navbar-fixed:not(.layout-menu-collapsed) .layout-content-navbar:not(.layout-without-menu) .layout-navbar {
                left: 0 !important;
            }

            .layout-navbar-fixed .layout-navbar.navbar-detached {
                width: 100% !important;
            }

        }

        @media screen and (max-width: 1024px) {
            .layout-menu-toggle a {
                padding-left: 20px !important;
            }

            .close-menu.mdi:before {
                font-size: 22px;
                line-height: 22px;
            }

            .pr {
                padding-right: 24px;
            }

            .dropdown-menu-end[data-bs-popper] {
                right: 24px;
                /* left: 18px !important; */
            }
        }

        @media screen and (max-width: 768px) {

            .pr {
                padding-right: 24px;
            }

            .dropdown-menu-end[data-bs-popper] {
                right: 16px;
                /* left: 18px !important; */
            }
        }
    </style>

    @yield('styles')

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    {{-- <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script> --}}
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('layouts.navigation')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('layouts.header')
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    {{-- <div class="container-xxl flex-grow-1 container-p-y"> --}}
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    @include('layouts.footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/swiper/swiper.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>
    <script src="{{ asset('assets/js/form-layouts.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        // Global SweetAlert policy: never show deny/no button.
        (function () {
            if (!window.Swal || typeof window.Swal.fire !== 'function') {
                return;
            }

            function normalizeArgs(args) {
                if (args.length === 1 && typeof args[0] === 'object' && args[0] !== null) {
                    args[0] = {
                        ...args[0],
                        showDenyButton: false
                    };
                }
                return args;
            }

            const originalFire = window.Swal.fire.bind(window.Swal);
            window.Swal.fire = function (...args) {
                return originalFire(...normalizeArgs(args));
            };

            if (typeof window.Swal.mixin === 'function') {
                const originalMixin = window.Swal.mixin.bind(window.Swal);
                window.Swal.mixin = function (...mixinArgs) {
                    if (mixinArgs.length === 1 && typeof mixinArgs[0] === 'object' && mixinArgs[0] !== null) {
                        mixinArgs[0] = {
                            ...mixinArgs[0],
                            showDenyButton: false
                        };
                    }

                    const mixed = originalMixin(...mixinArgs);
                    if (mixed && typeof mixed.fire === 'function') {
                        const mixedFire = mixed.fire.bind(mixed);
                        mixed.fire = function (...args) {
                            return mixedFire(...normalizeArgs(args));
                        };
                    }
                    return mixed;
                };
            }
        })();
    </script>
    {{-- <script src="https://code.highcharts.com/highcharts.js"></script> --}}
    {{-- <script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB1mG52tiHM3duCJupl0CtEB3xpzUGiohQ&callback=initAutocomplete&libraries=places&v=weekly"
    defer
  ></script> --}}

    @yield('scripts')

    <script>
        $(document).ready(function() {

            $(".close-menu").click(function() {

                $("#layout-menu").hide();
                $(".layout-navbar-fixed").removeClass("layout-menu-expanded");
            });


            $(".layout-menu-toggle").click(function() {
                $("#layout-menu").show();
                $(".layout-navbar-fixed").addClass("layout-menu-expanded");
            });
            // layout-menu-toggle


        });
    </script>
    {{-- <script>
        function markAllAsRead() {
            fetch('/notifications/mark-all-as-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelectorAll('.dropdown-notifications-item').forEach(item => {
                            item.classList.add('marked-as-read');
                        });
                        document.querySelector('.badge-dot').innerText = '0'; // Update badge count
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function markAsRead(notificationId) {
            fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`[data-id="${notificationId}"]`).classList.add('marked-as-read');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function archiveNotification(notificationId) {
            fetch(`/notifications/${notificationId}/archive`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                })
                .then(response => {
                    if (response.ok) {
                        document.querySelector(`[data-id="${notificationId}"]`).remove();
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script> --}}
</body>

</html>
