@extends('layouts.app')
@section('title', 'Activity Log')
@section('styles')
<style>
    .filter-card {
        background-color: #F4F7F6 !important;
        border: 1px solid #E0E6E4;
    }

    .filter-card .form-control,
    .filter-card .form-select,
    .filter-card .select2-selection {
        border-color: #E0E6E4 !important;
        color: #1F2933 !important;
        background-color: #fff !important;
    }

    .filter-card .form-control:focus,
    .filter-card .form-select:focus,
    .filter-card .select2-selection:focus {
        border-color: #4CC2B8 !important;
        box-shadow: 0 0 0 .2rem rgba(76, 194, 184, .2) !important;
    }

    .filter-card .btn-primary {
        background-color: #1F7A4C !important;
        border-color: #1F7A4C !important;
        color: #fff !important;
    }

    .filter-card .btn-primary:hover,
    .filter-card .btn-primary:focus,
    .filter-card .btn-primary:active {
        background-color: #2FAE7A !important;
        border-color: #2FAE7A !important;
        color: #fff !important;
    }

    div.dataTables_length {
        margin-left: -68px;
    }

    .paginate-info {
        font-size: 1rem;
        color: #000;
        font-weight: 500;
    }

    .responsive-table nav {
        background: none;
        height: 0;
    }

    .pagination .page-item {
        display: flex;
        align-items: center;
        padding-right: 10px;
    }

    .d-none {
        display: none;
    }

    .page-length-list {
        position: absolute;
        top: 85px;
        left: 0;
    }

    .card-datatable {
        min-height: 200px;
    }

    input[type="search"]::-webkit-search-decoration,
    input[type="search"]::-webkit-search-cancel-button,
    input[type="search"]::-webkit-search-results-button,
    input[type="search"]::-webkit-search-results-decoration {
        -webkit-appearance: none;
    }

    select#selPages {
        position: absolute;
        width: auto;
        z-index: 9;
        top: 68px;
    }

    .disabled>.page-link {
        background-color: transparent;
    }

    .form-floating-outline label::after .filter_label {
        background: #1F7A4C;
    }

    div.dataTables_wrapper div.col-sm-12 {
        padding: 0 !important;
    }

    /* Overlay styles */
    #overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 9999;
        display: block;
        overflow-x: hidden !important;
    }

    .addButton {
        color: #fff !important;
        border-color: #fff;
    }

    .addButton:hover {
        color: #1F7A4C !important;
        background-color: #fff !important;
    }

    .module-card-header {
        background-color: #1F7A4C !important;
        border-radius: 10px;
    }

    .module-card-header .card-title {
        color: #fff !important;
    }

    .change-status {
        text-transform: capitalize;
    }

    .btn-outline-success {
        color: green;
        border-color: green;
        background: transparent;
    }

    .btn-outline-success:hover {
        color: green !important;
        background-color: #00800033 !important;
        border-color: green !important;
    }

    [type="search"]::-webkit-search-cancel-button {
        -webkit-appearance: none;
        appearance: none;
        height: 10px;
        width: 10px;
        background-image: url("{{ asset('assets/img/branding/search-close.png') }}");
        background-size: 10px 10px;
    }

    div.dataTables_wrapper div.dataTables_length select {
        width: 80px;
    }

    .modal-body table td:last-child {
        word-break: break-all;
    }

    @media screen and (max-width:1366px) {
        .mobile-pagination {
            display: block !important;
            text-align: center;
        }

        ul.pagination {
            justify-content: center;
        }
    }

    @media screen and (max-width: 768px) {
        .mobile-pagination {
            flex-wrap: wrap;
            width: 100%;
            justify-content: center !important;
        }

        .mobile-pagination p {
            font-size: 15px;
            text-align: center;
        }

        .mobile-pagination li a {
            font-size: 13px !important;
        }

        .mobile-pagination li {
            padding-right: 0 !important;
        }

        .mobile-pagination li span {
            font-size: 13px;
            background: transparent !important;
        }

        .mobile-pagination li {
            background: transparent;
        }

        .mobile-pagination ul li.active span {
            background: #1F7A4C !important;
        }

        .mobile-pagination .pagination {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        select#selPages {
            position: static;
            width: auto;
            top: 115px;
            /* margin: 13px auto 0; */
        }
    }

    @media screen and (max-width: 425px) {
        select#selPages {
            margin: 13px auto 0;
        }

        .modal-dialog {
            display: flex;
            align-items: center;
            min-height: calc(100% - var(--bs-modal-margin)* 2);
        }
    }
</style>
@endsection
@section('content')
<div class="card mb-3 filter-card">
    <div class="card-body">
        <form id="filter_form">
            <input type="hidden" name="page_length" id="page_length" value="{{ $request->page_length }}">
            <div class="row">
                <div class="col-lg-2 col-md-3 col-12 mb-4 mb-md-0">
                    <div class="form-floating form-floating-outline">
                        <input type="text" class="form-control" placeholder="DD/MM/YYYY" id="from"
                            name="from" value="{{ $request->from }}" autocomplete="off" />
                        <label for="dob" style="color: #1F7A4C;">From</label>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-12 mb-4 mb-md-0">
                    <div class="form-floating form-floating-outline">
                        <input type="text" class="form-control" placeholder="DD/MM/YYYY" id="to"
                            name="to" value="{{ $request->to }}" autocomplete="off" />
                        <label for="dob" style="color: #1F7A4C;">To</label>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-12 mb-4 mb-md-0">
                    <div class="form-floating form-floating-outline">
                        <select class="form-select select2" id="moduleId" name="moduleId"
                            data-placeholder="Select Module" data-allow-clear="true">
                            <option value="" selected>Select Module</option>
                            @foreach ($modules as $module)
                            <option value="{{ $module }}" @if ($request->moduleId == $module) selected @endif>
                                {{ $module }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-12 mb-4 mb-md-0">
                    <div class="form-floating form-floating-outline">
                        <select class="form-select select2" name="actionId" id="actionId"
                            data-placeholder="Select Action" data-allow-clear="true">
                            <option value="" selected>Select Action</option>
                            @foreach ($actions as $action)
                            <option value="{{ $action }}" @if ($request->actionId == $action) selected @endif>
                                {{ ucfirst($action) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-12 d-flex align-items-center gap-2">
                    <button class="btn btn-primary" type="submit" id="filter" name="filter">Filter</button>
                    <button class="btn btn-primary" type="submit" name="reset" id="reset">Reset</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between py-3 module-card-header">
        <h5 class="card-title m-0 me-2 text-secondary">Activity Log</h5>
    </div>

    <div class="card-body pt-0">
        <div class="card-datatable table-responsive pt-0">
            <div class="row page-wrapper">
                <div class="col-md-1">
                    <select id="selPages" class="form-select">
                        @foreach ([10, 25, 50, 100, 200, 500, 1000, 5000, 10000] as $value)
                        <option @if ($request->page_length == $value) selected @endif value="{{ $value }}">
                            {{ $value }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <table class="datatables-basic table table-striped" id="donation_table">
                <thead>
                    <tr>
                        <th></th>
                        <!-- <th>Sr No.</th> -->
                        <th>DateTime</th>
                        <th>Action</th>
                        <th>Module Name</th>
                        <th>Action By</th>
                        <th>Description</th>
                        <th>Old Values</th>
                        <th>New Values</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($allData as $key => $item)
                    <tr>
                        <td></td>
                        <!-- <td>{{ $key + 1 }}</td> -->
                        <td>{{ $item->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ ucfirst($item->event) }}</td>
                        <td>{{ $item->log_name }}</td>
                        <td>{{ $item->user->name ?? '' }}</td>
                        <td>{{ $item->description }}</td>
                        {{-- Old Values Column --}}
                        <td>
                            @if ($item->properties)
                            @php
                            $activityLog = json_decode($item->properties, true);
                            $oldsString = '';
                            if (isset($activityLog['old'])) {
                            foreach ($activityLog['old'] as $key => $value) {
                            $displayValue = is_array($value) ? json_encode($value) : $value;
                            $oldsString .= $key . ' - ' . $displayValue . ', ';
                            }
                            $oldsString = rtrim($oldsString, ', ');
                            }
                            @endphp
                            {!! $oldsString !!}
                            @endif
                        </td>
                        {{-- New Values Column --}}
                        <td>
                            @if ($item->properties)
                            @php 
                            $attributesString = '';
                            if (isset($activityLog['attributes'])) {
                            foreach ($activityLog['attributes'] as $key => $value) {
                            $displayValue = is_array($value) ? json_encode($value) : $value;
                            $attributesString .= $key . ' - ' . $displayValue . ', ';
                            }
                            $attributesString = rtrim($attributesString, ', ');
                            }
                            @endphp
                            {!! $attributesString !!}
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="row page-wrapper">
                <div class="col col-md-12">
                    {!! $allData->withQueryString()->links('pagination::bootstrap-5') !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        setTimeout(function() {
            $('#overlay').css('display', 'none');
        }, 100);

        $('#from').flatpickr({
            dateFormat: 'd/m/Y'
        });
        $('#to').flatpickr({
            dateFormat: 'd/m/Y'
        });

        $(".pagination li:first-child .page-link").text("Previous");
        $(".pagination li:last-child .page-link").text("Next");

        var urlQueryParams = location.search.slice(1);
        var urlParams = new URLSearchParams(window.location.search);

        var from = urlParams.get('from') ?? "";
        var to = urlParams.get('to') ?? "";
        var actionId = urlParams.get('actionId') ?? "";
        var moduleId = urlParams.get('moduleId') ?? "";
        var paymentType = urlParams.get('paymentType') ?? "";
        var search = urlParams.get('search') ?? "";

        $('.page-item a.page-link').each(function(key, value) {
            let page = value.href.split('?')[1];
            let pageLength = urlParams.get('page_length') ? urlParams.get('page_length') : 10;
            let from = urlParams.get('from') ?? "";
            let to = urlParams.get('to') ?? "";
            let actionId = urlParams.get('actionId') ?? "";
            let moduleId = urlParams.get('moduleId') ?? "";
            let paymentType = urlParams.get('paymentType') ?? "";
            let baseUrl = value.href.split('?')[0];
            if (page || pageLength) {
                $(this).attr('href', baseUrl + '?' + page + '&page_length=' + pageLength + '&from=' +
                    from + '&to=' + to + '&actionId=' + actionId + '&moduleId=' +
                    moduleId + '&paymentType=' + paymentType);
            } else {
                $(this).attr('href', value.href);
            }
        });

        $('#selPages').on('change', function() {
            var pageLength = $(this).val();
            $('#page_length').val(pageLength);
            $('#filter_form').submit().trigger('click');
        });

        $('#filter').click(function() {
            fill_datatable();
        });

        $('#reset').click(function() {
            $('#from').val('');
            $('#to').val('');
            $('#actionId').val('').trigger('change');
            $('#moduleId').val('').trigger('change');
            $('#paymentType').val('').trigger('change');
            $('#donation_table').DataTable().destroy();
            fill_datatable();
        });

        fill_datatable();

        /* $('.dataTables_filter input').on('input', function (e) {
            var inputValue = $(this).val();
            var currentPage = $('.dataTables_paginate .paginate_button.current').text(); // Get current page number

            if (inputValue.length >= 5) {
                urlParams.set("search", inputValue);
                urlParams.set("page", currentPage); // Set current page number in URL
                window.location.search = urlParams.toString();
            }
            else if (inputValue.length === 0) {
                urlParams.set("search", inputValue);
                urlParams.set("page", currentPage); // Set current page number in URL
                window.location.search = urlParams.toString();
            }
        }); */

        $('.dataTables_filter input').keypress(function(e) {
            if (e.which == 13) { // Check if Enter key was pressed
                var inputValue = $(this).val();
                var currentPage = $('.dataTables_paginate .paginate_button.current')
                    .text(); // Get current page number

                urlParams.set("search", inputValue);
                urlParams.set("page", currentPage); // Set current page number in URL
                window.location.search = urlParams.toString();
            }
        });
        $('.dataTables_filter input').focus();

        function fill_datatable() {
            if ($.fn.dataTable.isDataTable('#donation_table')) {
                dataTable.destroy();
            }
            dataTable = $('#donation_table').DataTable({
                fixedHeader: {
                    header: true
                },
                "autoWidth": true,
                deferRender: true,
                bPaginate: false,
                "lengthChange": false,
                "info": false,
                searching: true,
                scrollX: true,
                processing: true,
                serverSide: false,
                sorting: false,
                ordering: false,
                lengthMenu: [10, 25, 50, 100, 200, 500],
                columnDefs: [{
                        // For Responsive
                        className: 'control',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 1,
                        targets: 0,
                        render: function() {
                            return '';
                        }
                    }, { // Ensure "Action" appears first in mobile
                        targets: 1,
                        responsivePriority: 3
                    },
                    { // Ensure "Name" appears second in mobile
                        targets: 4,
                        responsivePriority: 2
                    },
                    { // Reduce priority for other columns (they appear after Action & Name in mobile)
                        targets: [0, 2, 4, 5, 6],
                        responsivePriority: 99
                    }
                ],
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.modal({
                            header: function(row) {
                                var data = row.data();
                                return 'Details of ' + data[4] + ' ' + data[3];
                            }
                        }),
                        type: 'column',
                        renderer: function(api, rowIdx, columns) {
                            var data = $.map(columns, function(col, i) {
                                return col.title !==
                                    '' // ? Do not show row in modal popup if title is blank (for check box)
                                    ?
                                    '<tr data-dt-row="' +
                                    col.rowIndex +
                                    '" data-dt-column="' +
                                    col.columnIndex +
                                    '">' +
                                    '<td>' +
                                    col.title +
                                    ':' +
                                    '</td> ' +
                                    '<td class="word-break-td">' +
                                    col.data +
                                    '</td>' +
                                    '</tr>' :
                                    '';
                            }).join('');

                            return data ? $('<table class="table"/><tbody />').append(data) : false;
                        }
                    }
                }
            });
            $('.dataTables_filter input').val(search);
        }

        setTimeout(function() {
            $('.alert').fadeOut('fast');
        }, 3000);


    });
</script>
@endsection
