<?php
require_once '../partials/main.php';

use SLSupplyHub\Address;
// Initialize models
$addressModel = new Address();

// Get saved addresses
$addresses = $addressModel->getUserAddresses($session->getUserId());

// Initialize barangays data
$barangaysByCity = [
    'Maasin City' => [
        'Abgao',
        'Asuncion',
        'Baguio District',
        'Bahay',
        'Banday',
        'Bantawan',
        'Basak',
        'Batuan',
        'Bilibol',
        'Cabadiangan',
        'Cabulihan',
        'Cagnituan',
        'Cambooc',
        'Canturing',
        'Combado',
        'Dongon',
        'Guadalupe',
        'Hanginan',
        'Ibarra',
        'Isagani',
        'Labrador',
        'Lib-og',
        'Lonoy',
        'Lunas',
        'Mabini',
        'Mahayahay',
        'Maintinop',
        'Mamingao',
        'Maria Clara',
        'Nasaug',
        'Nonok Norte',
        'Nonok Sur',
        'Panan-awan',
        'Pasay',
        'San Agustin',
        'San Francisco',
        'San Isidro',
        'San Jose',
        'San Rafael',
        'Santa Cruz',
        'Santa Rosa',
        'Soro-soro'
    ],
    'Macrohon' => [
        'Aguinaldo',
        'Amparo',
        'Bagong Silang',
        'Buscayan',
        'Cambaro',
        'Flordeliz',
        'Ichon',
        'Ilihan',
        'Lapu-lapu',
        'Lower Villa Jacinta',
        'Maujo',
        'Molopolo',
        'Rizal',
        'San Isidro',
        'San Joaquin',
        'San Roque',
        'Santa Cruz',
        'Upper Villa Jacinta'
    ],
    'Padre Burgos' => [
        'Buenavista',
        'La Purisima Concepcion',
        'Lungsodaan',
        'San Juan',
        'Santo Rosario'
    ],
    'Liloan' => [
        'Asuncion',
        'Caligangan',
        'Candayuman',
        'Cata-ata',
        'Estela',
        'Fatima',
        'Himayangan',
        'Magbagacay',
        'Pres. Quezon',
        'Pres. Roxas',
        'San Isidro',
        'San Roque',
        'Tabugon'
    ],
    'Sogod' => [
        'Buac',
        'Consolacion',
        'Hibod-hibod',
        'Hipantag',
        'Javier',
        'Kahupian',
        'Kanangkaan',
        'Kauswagan',
        'La Paz',
        'Libas',
        'Magatas',
        'Malinao',
        'Maria Plana',
        'Milagroso',
        'Olisihan',
        'Pancho Villa',
        'Poblacion',
        'Rizal',
        'San Francisco',
        'San Isidro',
        'San Jose',
        'San Juan',
        'San Miguel',
        'San Pedro',
        'San Roque',
        'San Vicente',
        'Santa Maria',
        'Suba',
        'Tampoong',
        'Zone I',
        'Zone II',
        'Zone III'
    ],
    'Bontoc' => [
        'Anahawan',
        'Beniton',
        'Bocawe',
        'Canlupao',
        'Casao',
        'Divisoria',
        'Himakilo',
        'Hilaan',
        'Lawgawan',
        'Mahayahay',
        'Paku',
        'Poblacion',
        'San Vicente',
        'Taa',
        'Union'
    ],
    'Saint Bernard' => [
        'Ayahag',
        'Bantawon',
        'Cabaan',
        'Cahumpan',
        'Carnaga',
        'Catmon',
        'Guinsaugon',
        'Himatagon',
        'Hindag-an',
        'Hinagtikan',
        'Kauswagan',
        'Lipanto',
        'Magbagakay',
        'Mahayag',
        'Malinao',
        'Nueva Esperanza',
        'Poblacion',
        'San Isidro',
        'Sug-angon',
        'Tabontabon'
    ],
    'San Juan' => [
        'Agbao',
        'Basak',
        'Bobon A',
        'Bobon B',
        'Bothoan',
        'Bugho',
        'Canturing',
        'Garrido',
        'Minoyho',
        'Pong-oy',
        'San Jose',
        'San Vicente',
        'Santa Cruz',
        'Santo Niño',
        'Sogod'
    ],
    'Silago' => [
        'Badiangon',
        'Balagawan',
        'Catmon',
        'Hingatungan',
        'Poblacion',
        'Salvacion',
        'Mercedes',
        'Sudmon',
        'Talisay',
        'Tubod'
    ],
    'Hinunangan' => [
        'Amparo',
        'Bangcas A',
        'Bangcas B',
        'Biasong',
        'Calag-itan',
        'District I',
        'District II',
        'District III',
        'District IV',
        'Ilaya',
        'Ingan',
        'Lungsodaan',
        'Nava',
        'Nueva Esperanza',
        'Palongpong',
        'Poblacion',
        'Sagbok',
        'San Isidro',
        'San Juan',
        'Song-on',
        'Talisay',
        'Tahusan'
    ],
    'Hinundayan' => [
        'Ambao',
        'Sagbok',
        'Poblacion',
        'Baculod',
        'Cabulisan',
        'Ingan',
        'Lawgawan',
        'Linao',
        'Manalog',
        'Plaridel',
        'San Roque',
        'Salog'
    ],
    'Anahawan' => [
        'Amagusan',
        'Calintaan',
        'Canlabian',
        'Cogon',
        'Poblacion',
        'San Vicente',
        'Tagup-on'
    ],
    'San Francisco' => [
        'Anislagon',
        'Bongbong',
        'Central',
        'Habay',
        'Marayag',
        'Pinamudlan',
        'Santa Cruz',
        'Tinaan'
    ],
    'Pintuyan' => [
        'Balongbalong',
        'Buenavista',
        'Poblacion',
        'Mainit',
        'Manglit',
        'San Roque',
        'Santo Rosario'
    ],
    'San Ricardo' => [
        'Benit',
        'Bitoon',
        'Cabutan',
        'Esperanza',
        'Looc',
        'Poblacion',
        'San Antonio',
        'San Ramon',
        'Timba'
    ]
];

$postalCodes = [
    'Maasin City' => '6600',
    'Macrohon' => '6601',
    'Padre Burgos' => '6602',
    'Liloan' => '6603',
    'Sogod' => '6604',
    'Bontoc' => '6604',
    'Saint Bernard' => '6610',
    'San Juan' => '6611',
    'Silago' => '6606',
    'Hinunangan' => '6608',
    'Hinundayan' => '6609',
    'Anahawan' => '6610',
    'San Francisco' => '6611',
    'Pintuyan' => '6612',
    'San Ricardo' => '6613'
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $title = "My Addresses";
    $page_title = "My Addresses";
    include '../partials/title-meta.php';
    include '../partials/head-css.php';
    ?>
    <link href="<?= asset_url('libs/selectize/css/selectize.bootstrap3.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= asset_url('libs/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet" type="text/css" />
</head>

<body>
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        <div class="content-page">
            <?php include 'topbar.php'; ?>

            <div class="content">
                <div class="container-fluid">
                    <?php include '../partials/page-title.php'; ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="header-title">My Addresses</h4>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                            <i class="mdi mdi-plus"></i> Add New Address
                                        </button>
                                    </div>

                                    <div class="row" id="addresses-container">
                                        <?php if (!empty($addresses)): ?>
                                            <?php foreach ($addresses as $address): ?>
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <div class="card border <?= $address['is_default'] ? 'border-primary' : '' ?>">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                                <h5 class="card-title mb-0">
                                                                    <?= htmlspecialchars($address['first_name'] . ' ' . $address['last_name']) ?>
                                                                    <?php if ($address['is_default']): ?>
                                                                        <span class="badge bg-primary ms-2">Default</span>
                                                                    <?php endif; ?>
                                                                </h5>
                                                                <div class="dropdown">
                                                                    <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                                                                        <i class="mdi mdi-dots-vertical font-18"></i>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-end">
                                                                        <button class="dropdown-item edit-address" 
                                                                                data-address='<?= json_encode($address) ?>'>
                                                                            <i class="mdi mdi-pencil me-1"></i> Edit
                                                                        </button>
                                                                        <?php if (!$address['is_default']): ?>
                                                                            <button class="dropdown-item set-default" 
                                                                                    data-id="<?= $address['id'] ?>">
                                                                                <i class="mdi mdi-star me-1"></i> Set as Default
                                                                            </button>
                                                                        <?php endif; ?>
                                                                        <button class="dropdown-item text-danger delete-address" 
                                                                                data-id="<?= $address['id'] ?>">
                                                                            <i class="mdi mdi-trash-can me-1"></i> Delete
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <p class="card-text mb-1">
                                                                <?= htmlspecialchars($address['street']) ?>
                                                            </p>
                                                            <p class="card-text mb-1">
                                                                <?= htmlspecialchars($address['barangay'] . ', ' . $address['city']) ?>
                                                            </p>
                                                            <p class="card-text mb-1">
                                                                Postal Code: <?= htmlspecialchars($address['postal_code']) ?>
                                                            </p>
                                                            <p class="card-text mb-1">
                                                                Phone: <?= htmlspecialchars($address['phone']) ?>
                                                            </p>
                                                            <p class="card-text">
                                                                Email: <?= htmlspecialchars($address['email']) ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="col-12">
                                                <div class="text-center p-4">
                                                    <i class="mdi mdi-map-marker-off text-muted" style="font-size: 48px;"></i>
                                                    <h4 class="mt-3">No Addresses Found</h4>
                                                    <p class="text-muted">You haven't added any delivery addresses yet.</p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Address Modal -->
            <div class="modal fade" id="addAddressModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addressModalTitle">Add New Address</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addressForm">
                                <input type="hidden" id="address_id" name="address_id">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control" id="phone" name="phone" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="city" class="form-label">City/Municipality <span class="text-danger">*</span></label>
                                            <select class="form-control selectize" id="city" name="city" required>
                                                <option value="">Select City</option>
                                                <?php foreach (array_keys($barangaysByCity) as $city): ?>
                                                    <option value="<?= $city ?>"><?= $city ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="barangay" class="form-label">Barangay <span class="text-danger">*</span></label>
                                            <select class="form-control selectize" id="barangay" name="barangay" required>
                                                <option value="">Select Barangay</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="street" class="form-label">Street Address <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="street" name="street" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="postal_code" class="form-label">Postal Code</label>
                                            <input type="text" class="form-control" id="postal_code" name="postal_code" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_default" name="is_default">
                                        <label class="form-check-label" for="is_default">Set as default address</label>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="saveAddress">Save Address</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php include '../partials/footer.php'; ?>
        </div>
    </div>

    <?php include '../partials/right-sidebar.php'; ?>
    <?php include '../partials/footer-scripts.php'; ?>
    
    <!-- Additional Scripts -->
    <script src="<?= asset_url('libs/selectize/js/standalone/selectize.min.js') ?>"></script>
    <script src="<?= asset_url('libs/sweetalert2/sweetalert2.min.js') ?>"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const barangaysByCity = <?= json_encode($barangaysByCity) ?>;
            const postalCodes = <?= json_encode($postalCodes) ?>;
            let citySelect, barangaySelect;

            // Initialize Selectize
            function initializeSelects() {
                citySelect = $('#city').selectize({
                    theme: 'bootstrap3',
                    create: false
                })[0].selectize;

                barangaySelect = $('#barangay').selectize({
                    theme: 'bootstrap3',
                    create: false
                })[0].selectize;

                // City Change Handler
                $('#city').on('change', function() {
                    updateBarangaysAndPostalCode($(this).val());
                });
            }

            function updateBarangaysAndPostalCode(city) {
                const postalCodeInput = $('#postal_code');

                // Update barangays
                barangaySelect.clearOptions();
                barangaySelect.addOption({ value: '', text: 'Select Barangay' });

                if (barangaysByCity[city]) {
                    barangaysByCity[city].forEach(barangay => {
                        barangaySelect.addOption({
                            value: barangay,
                            text: barangay
                        });
                    });
                }
                barangaySelect.refreshItems();

                // Update postal code
                postalCodeInput.val(postalCodes[city] || '');
            }

            // Initialize selectize
            initializeSelects();

            // Handle form submission
            $('#saveAddress').click(function() {
                const form = $('#addressForm');
                if (!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }

                const formData = new FormData(form[0]);
                const addressId = $('#address_id').val();
                const url = 'ajax/address-' + (addressId ? 'update' : 'add') + '.php';

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: addressId ? 'Address updated successfully.' : 'Address added successfully.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to save address.'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to save address. Please try again.'
                        });
                    }
                });
            });

            // Edit address
            $('.edit-address').click(function() {
                const address = $(this).data('address');
                $('#addressModalTitle').text('Edit Address');
                $('#address_id').val(address.id);
                $('#first_name').val(address.first_name);
                $('#last_name').val(address.last_name);
                $('#email').val(address.email);
                $('#phone').val(address.phone);
                $('#street').val(address.street);
                $('#is_default').prop('checked', address.is_default);

                citySelect.setValue(address.city);
                updateBarangaysAndPostalCode(address.city);
                setTimeout(() => {
                    barangaySelect.setValue(address.barangay);
                }, 100);

                $('#addAddressModal').modal('show');
            });

            // Delete address
            $('.delete-address').click(function() {
                const addressId = $(this).data('id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('ajax/address-delete.php', { address_id: addressId })
                            .done(function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: 'Address has been deleted.',
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message || 'Failed to delete address.'
                                    });
                                }
                            })
                            .fail(function(xhr) {
                                console.error(xhr.responseText);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to delete address. Please try again.'
                                });
                            });
                    }
                });
            });

            // Set default address
            $('.set-default').click(function() {
                const addressId = $(this).data('id');
                
                $.post('ajax/address-set-default.php', { address_id: addressId })
                    .done(function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Default address updated.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to update default address.'
                            });
                        }
                    })
                    .fail(function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to update default address. Please try again.'
                        });
                    });
            });

            // Reset form when modal is closed
            $('#addAddressModal').on('hidden.bs.modal', function() {
                $('#addressForm')[0].reset();
                $('#address_id').val('');
                $('#addressModalTitle').text('Add New Address');
                citySelect.clear();
                barangaySelect.clear();
                barangaySelect.clearOptions();
            });
        });
    </script>
</body>
</html>