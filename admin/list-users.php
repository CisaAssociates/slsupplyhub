<?php include '../partials/main.php' ?>

<head>
    <?php
    $title = "List User";
    $sub_title = "Menu";
    include '../partials/title-meta.php' ?>

    <?php include '../partials/head-css.php' ?>
    <link rel="stylesheet" href="<?= asset_url('libs/sweetalert2/sweetalert2.min.css') ?>">
    <link href="<?= asset_url('libs/toastr/build/toastr.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= asset_url('libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= asset_url('libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= asset_url('libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') ?>" rel="stylesheet" type="text/css" />
</head>

<body>

    <!-- Begin page -->
    <div id="wrapper">

        <?php include 'sidenav.php' ?>

        <div class="content-page">

            <?php include '../partials/topbar.php' ?>

            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">

                    <?php
                    $button = "<button class='btn btn-primary waves-effect waves-light btn-sm' 
                                        type='button'
                                        data-bs-toggle='modal' 
                                        data-bs-target='#add-user-modal'>  
                                    <i class='mdi mdi-plus'></i> Add User
                                </button>";
                    include '../partials/page-title.php';
                    ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">List of Users</h4>
                                    <table id="users-table" class="table table-hover w-100 display">
                                        <thead>
                                            <tr>
                                                <th>Fullname</th>
                                                <th>Email</th>
                                                <th>Phone Number</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- container -->

            </div> <!-- content -->

            <?php include '../partials/footer.php' ?>
        </div>

    </div>
    <!-- END wrapper -->

    <?php include '../partials/right-sidebar.php' ?>

    <?php include '../partials/footer-scripts.php' ?>

    <script src="<?= asset_url('libs/sweetalert2/sweetalert2.all.min.js') ?>"></script>
    <script src="<?= asset_url('libs/datatables.net/js/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= asset_url('libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') ?>"></script>
    <script src="<?= asset_url('libs/datatables.net-responsive/js/dataTables.responsive.min.js') ?>"></script>
    <script src="<?= asset_url('libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') ?>"></script>
    <script src="<?= asset_url('libs/datatables.net-keytable/js/dataTables.keyTable.min.js') ?>"></script>
    <script src="<?= asset_url('libs/toastr/build/toastr.min.js') ?>"></script>

    <script>
        $(document).ready(function() {
            // Initialize toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

            // Initialize DataTable
            var table = $('#users-table').DataTable({
                ajax: {
                    url: 'ajax/users.php',
                    type: 'POST',
                    data: function(d) {
                        return {
                            ...d,
                            action: 'list'
                        };
                    },
                    dataSrc: function(json) {
                        if (!json.success) {
                            toastr.error(json.message || 'An error occurred');
                            return [];
                        }
                        return json.data || [];
                    }
                },
                columns: [
                    { data: 'fullname' },
                    { data: 'email' },
                    { data: 'phone' },
                    { data: 'role',
                        render: function(data) {
                            return '<span class="badge bg-primary text-capitalize   ">' + data + '</span>';
                        }
                     },
                    { 
                        data: 'status',
                        render: function(data) {
                            return data == 1 ? 
                                '<span class="badge bg-success">Active</span>' : 
                                '<span class="badge bg-danger">Inactive</span>';
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            return `
                                <button class="btn btn-sm btn-info edit-user" data-id="${data.id}">
                                    <i class="mdi mdi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-user" data-id="${data.id}">
                                    <i class="mdi mdi-trash-can"></i>
                                </button>
                            `;
                        }
                    }
                ],
                responsive: true,
                processing: true,
                serverSide: false,
                language: {
                    processing: '<i class="mdi mdi-spin mdi-loading"></i> Loading...'
                }
            });

            // Add User Form Submit
            $('#add-form-user').on('submit', function(e) {
                e.preventDefault();

                // Password validation
                var password = $('#password').val();
                var confirmPassword = $('#confirm-password').val();

                if (password !== confirmPassword) {
                    toastr.error('Passwords do not match!');
                    return;
                }

                if (password.length < 8) {
                    toastr.error('Password must be at least 8 characters long!');
                    return;
                }

                var $submitBtn = $(this).find('button[type="submit"]');
                var originalText = $submitBtn.html();
                $submitBtn.html('<i class="mdi mdi-spin mdi-loading"></i> Saving...').prop('disabled', true);

                $.ajax({
                    url: 'ajax/users.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'create',
                        fullname: $('#fullname').val().trim(),
                        email: $('#email').val().trim(),
                        phone: $('#number').val().trim(),
                        role: $('#role').val(),
                        password: password
                    },
                    success: function(response) {
                        if (response.success === true) {
                            $('#add-user-modal').modal('hide');
                            table.ajax.reload();
                            toastr.success(response.message);
                            $('#add-form-user')[0].reset();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('An error occurred while processing your request.');
                        console.error('Error:', error);
                    },
                    complete: function() {
                        $submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Edit User
            $(document).on('click', '.edit-user', function() {
                var id = $(this).data('id');
                var row = table.row($(this).closest('tr')).data();

                $('#edt-fullname').val(row.fullname);
                $('#edt-email').val(row.email);
                $('#edt-number').val(row.phone);
                $('#edt-role').val(row.role);
                $('#edt-form-user').data('id', id);

                $('#edt-user-modal').modal('show');
            });

            // Edit User Form Submit
            $('#edt-form-user').on('submit', function(e) {
                e.preventDefault();
                var id = $(this).data('id');

                var $submitBtn = $(this).find('button[type="submit"]');
                var originalText = $submitBtn.html();
                $submitBtn.html('<i class="mdi mdi-spin mdi-loading"></i> Saving...').prop('disabled', true);

                $.ajax({
                    url: 'ajax/users.php',
                    type: 'POST',
                    data: {
                        action: 'update',
                        id: id,
                        fullname: $('#edt-fullname').val(),
                        email: $('#edt-email').val(),
                        phone: $('#edt-number').val(),
                        role: $('#edt-role').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#edt-user-modal').modal('hide');
                            table.ajax.reload();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('An error occurred while processing your request.');
                    },
                    complete: function() {
                        $submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Delete User
            $(document).on('click', '.delete-user', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return $.ajax({
                            url: 'ajax/users.php',
                            type: 'POST',
                            data: {
                                action: 'delete',
                                id: id
                            }
                        }).then(response => {
                            if (!response.success) {
                                throw new Error(response.message);
                            }
                            return response;
                        }).catch(error => {
                            Swal.showValidationMessage(
                                `Request failed: ${error.message || 'Unknown error'}`
                            );
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        table.ajax.reload();
                        toastr.success(result.value.message);
                    }
                });
            });

            // Toggle Status
            $(document).on('click', '.toggle-status', function() {
                var $btn = $(this);
                var id = $btn.data('id');

                $btn.html('<i class="mdi mdi-spin mdi-loading"></i>').prop('disabled', true);

                $.ajax({
                    url: 'ajax/users.php',
                    type: 'POST',
                    data: {
                        action: 'toggle-status',
                        id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            table.ajax.reload();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                            $btn.html('<i class="mdi mdi-power"></i>').prop('disabled', false);
                        }
                    },
                    error: function() {
                        toastr.error('An error occurred while processing your request.');
                        $btn.html('<i class="mdi mdi-power"></i>').prop('disabled', false);
                    }
                });
            });

            // Form validation for required fields
            $('form').on('submit', function() {
                var valid = true;
                $(this).find('[required]').each(function() {
                    if (!$(this).val()) {
                        valid = false;
                        toastr.error($(this).prev('label').text() + ' is required');
                    }
                });
                return valid;
            });

            // Reset forms on modal close
            $('.modal').on('hidden.bs.modal', function() {
                $(this).find('form')[0].reset();
            });
        });
    </script>
</body>

</html>

<div id="add-user-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="add-user-modal" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add User</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="add-form-user">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="fullname" class="form-label">Fullname</label>
                                <input type="text" class="form-control" id="fullname" placeholder="John Doe" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" placeholder="john@example.com" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="number" class="form-label">Phone Number</label>
                                <input type="number" class="form-control" id="number" placeholder="number" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" required>
                                    <option value="">Select Role</option>
                                    <option value="supplier">Supplier</option>
                                    <option value="driver">Driver</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" placeholder="Enter password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirm-password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm-password" placeholder="Confirm your password" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info waves-effect">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div><!-- /.modal -->

<div id="edt-user-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="edt-user-modal" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit User</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="edt-form-user">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edt-fullname" class="form-label">Fullname</label>
                                <input type="text" class="form-control" id="edt-fullname" placeholder="John Doe" required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edt-email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edt-email" placeholder="john@example.com" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edt-number" class="form-label">Phone Number</label>
                                <input type="number" class="form-control" id="edt-number" placeholder="number" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edt-role" class="form-label">Role</label>
                                <select class="form-select" id="edt-role" required>
                                    <option value="">Select Role</option>
                                    <option value="supplier">Supplier</option>
                                    <option value="driver">Driver</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info waves-effect">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div><!-- /.modal -->