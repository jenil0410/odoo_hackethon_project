@extends('layouts.app')
@section('title', 'User Listing')
@section('styles')
    <style>
        #overlay {
            height: 100%;
            width: 100%;
            position: absolute;
            top: 0px;
            left: 0px;
            z-index: 99999;
            background-color: #000;
            filter: alpha(opacity=75);
            -moz-opacity: 0.75;
            opacity: 0.3;
            border-radius: 10px;
            display: none;
        }

        .dataTables_processing {
            z-index: 99999;
        }

        .addButton {
            color: #fff !important;
            background-color: transparent !important;
            border: 1px solid #fff !important;
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
            background: transparent !important;
            border: 0 !important;
            border-radius: 0 !important;
            padding: 0 !important;
        }

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

        #user_table.dataTable thead th {
            background-color: #1F7A4C !important;
            color: #fff !important;
            border-bottom-color: #1F7A4C !important;
        }

        .light-style .swal2-container {
            z-index: 99999;
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

        div.dataTables_wrapper div.col-sm-12 {
            padding: 0 !important;
        }

        .dt-button-collection {
            min-width: 223px !important;
            background-color: #fff !important;
            border: 1px solid #ddd !important;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2) !important;
            padding: 10px !important;
        }

        .dt-button-collection .dt-button.buttons-columnVisibility {
            display: block !important;
            width: 100% !important;
            text-align: left !important;
            padding: 6px 10px !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            background: #fff !important;
            color: #1F2933 !important;
        }

        .dt-button-collection .dt-button.buttons-columnVisibility:hover,
        .dt-button-collection .dt-button.buttons-columnVisibility.active {
            background-color: rgba(47, 174, 122, 0.12) !important;
            color: #1F2933 !important;
        }

        @media screen and (max-width: 425px) {
            .modal-dialog {
                display: flex;
                align-items: center;
                min-height: calc(100% - var(--bs-modal-margin)* 2);
            }
        }
    
        /* Datatable dropdown positioning per reference */
        .dt-button-collection {
            position: absolute !important;
            top: auto !important;
            left: auto !important;
            right: 163px !important;
            z-index: 1050 !important;
        }

        .dt-button-collection.dtb-b2 {
            position: absolute !important;
            top: auto !important;
            left: auto !important;
            right: 6px !important;
            z-index: 1050 !important;
            min-width: 150px !important;
        }
    </style>
@endsection
@section('content')
    <div class="card mb-3 filter-card">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label" for="role_id_filter">Role</label>
                    <select class="form-select select2" id="role_id_filter">
                        <option value="">All Roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="gender_filter">Gender</label>
                    <select class="form-select select2" id="gender_filter">
                        <option value="">All Genders</option>
                        @foreach ($genders as $gender)
                            <option value="{{ $gender }}">{{ ucfirst($gender) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary" type="button" id="applyUserFilters">Filter</button>
                    <button class="btn btn-outline-secondary" type="button" id="resetUserFilters">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between py-2 module-card-header">
            <h5 class="card-title m-0 me-2">User</h5>
            @if (\App\Models\Permission::checkCRUDPermissionToUser('User', 'create'))
                <a href="{{ route('user.create') }}" class="btn btn-primary waves-effect waves-light addButton">Add User</a>
            @endif
        </div>
        @if (session('message'))
            <div class="alert alert-{{ session('status') }} alert-dismissible fade show mb-0 mt-3" role="alert">
                <strong>{{ session('message') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="card-body">
            <div class="card-datatable table-responsive pt-0">
                <table class="datatables-basic table table-striped" id="user_table">
                    <div id="overlay"></div>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Action</th>
                            <th>Sr. No.</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Phone Number</th>
                            <th>Gender</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            let dataTable;
            const moduleTableButtons = [{
                    extend: 'colvis',
                    collectionLayout: 'fixed one-column',
                    columns: function(idx, data, node) {
                        const headerText = String($(node).text() || '').trim().toLowerCase();
                        return headerText !== '' && headerText !== '#' && headerText !== 'action';
                    },
                    text: '<i class="mdi mdi-eye me-1"></i> Select Columns',
                    className: 'btn btn-label-secondary'
                },
                {
                    extend: 'collection',
                    className: 'btn btn-label-primary dropdown-toggle me-2',
                    text: '<i class="mdi mdi-export-variant me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span>',
                    buttons: ['print', 'csv', 'excel', 'pdf', 'copy'].map(function(type) {
                        return {
                            extend: type,
                            className: 'dropdown-item',
                            exportOptions: {
                                columns: function(idx, data, node) {
                                    return $(node).is(':visible');
                                }
                            }
                        };
                    })
                }
            ];

            fill_datatable();

            $("#overlay").show();

            function fill_datatable(name = '', id = '', created_at = '') {
                dataTable = $('#user_table').DataTable({
                    searching: true,
                    processing: true,
                    serverSide: true,
                    scrollX: true,
                    lengthMenu: [10, 25, 50, 100, 1000, 10000],
                    dom: '<"flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    buttons: moduleTableButtons,
                    ajax: {
                        url: "{{ route('user.index') }}",
                        data: function(d) {
                            d.role_id_filter = $('#role_id_filter').val();
                            d.gender_filter = $('#gender_filter').val();
                        }
                    },
                    columns: [{
                            data: 'id'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'DT_RowIndex',
                            name: 'id'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'role_id',
                            name: 'role_id'
                        },
                        {
                            data: 'phone_number',
                            name: 'phone_number'
                        },
                        {
                            data: 'gender',
                            name: 'gender'
                        },
                        /* {
                            data: 'created_at',
                            name: 'created_at'
                        }, */
                    ],
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
                            targets: 3,
                            responsivePriority: 2
                        },
                        { // Reduce priority for other columns (they appear after Action & Name in mobile)
                            targets: [0, 2, 3],
                            responsivePriority: 99
                        }],
                    responsive: {
                        details: {
                            display: $.fn.dataTable.Responsive.display.modal({
                                header: function(row) {
                                    var data = row.data();
                                    return 'Details of ' + data['name'];
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
                                        '<td>' +
                                        col.data +
                                        '</td>' +
                                        '</tr>' :
                                        '';
                                }).join('');

                                return data ? $('<table class="table"/><tbody />').append(data) : false;
                            }
                        }
                    },
                    fnInitComplete: function() {
                        $("#overlay").hide();
                    },
                });

                let debounceTimer;
                $(".dataTables_filter input").off('input keyup').on("keyup", function(e) {
                    var searchTerm = this.value;
                    clearTimeout(debounceTimer);

                    if (searchTerm === "") {
                        dataTable.search("").draw();
                    } else {
                        debounceTimer = setTimeout(function() {
                            dataTable.search(searchTerm).draw();
                        }, 500);
                    }
                });
            }

            $('#applyUserFilters').on('click', function() {
                dataTable.ajax.reload();
            });

            $('#resetUserFilters').on('click', function() {
                $('#role_id_filter').val('').trigger('change');
                $('#gender_filter').val('').trigger('change');
                dataTable.ajax.reload();
            });

            setTimeout(function() {
                $('.alert').fadeOut('fast');
            }, 3000);

        });

        function deleteUser(id, name) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete " + name + ".",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                    cancelButton: 'btn btn-outline-secondary waves-effect'
                },
                buttonsStyling: false
            }).then(function(result) {

                if (result.value) {
                    $.ajax({
                        url: 'user/delete/' + id,
                        type: "get"
                    }).done(function(data) {
                        if (!data.status) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Cancelled!',
                                text: data.message,
                                customClass: {
                                    confirmButton: 'btn btn-primary waves-effect'
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: data.message,
                                customClass: {
                                    confirmButton: 'btn btn-primary waves-effect'
                                }
                            });
                            $('#user_table').DataTable().ajax.reload();
                        }
                    }).fail(function(jqXHR, ajaxOptions, thrownError) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cancelled!',
                            text: 'Something wrong.',
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect'
                            }
                        });
                    })
                } else {
                    Swal.fire({
                        title: 'Cancelled!',
                        text: 'Record is safe',
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            });
        }
    </script>
@endsection
