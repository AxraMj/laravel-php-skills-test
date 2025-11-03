<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Product Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px 0;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        .table {
            margin-bottom: 0;
        }
        .btn-edit {
            margin-right: 5px;
        }
        .sum-total-row {
            font-weight: bold;
            background-color: #e9ecef;
        }
        .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Product Management System</h1>

        <!-- Form Card -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Add New Product</h5>
            </div>
            <div class="card-body">
                <form id="productForm">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="product_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="quantity" class="form-label">Quantity in Stock</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Price per Item</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
                    <button type="button" class="btn btn-secondary" id="cancelEditBtn" style="display:none;">Cancel Edit</button>
                </form>
                <input type="hidden" id="editingId" value="">
            </div>
        </div>

        <!-- Products Table Card -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Product List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity in Stock</th>
                                <th>Price per Item</th>
                                <th>Datetime Submitted</th>
                                <th>Total Value Number</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                            <tr>
                                <td colspan="6" class="text-center">Loading products...</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr id="sumTotalRow" class="sum-total-row" style="display:none;">
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td id="sumTotalValue"><strong></strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (for easier AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {
            // Set up CSRF token for AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Load products on page load
            loadProducts();

            // Form submission
            $('#productForm').on('submit', function(e) {
                e.preventDefault();
                
                const editingId = $('#editingId').val();
                const formData = {
                    product_name: $('#product_name').val(),
                    quantity: $('#quantity').val(),
                    price: $('#price').val()
                };

                let url = '{{ route("products.store") }}';
                let method = 'POST';

                if (editingId) {
                    url = `/products/${editingId}`;
                    method = 'PUT';
                }

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            // Reset form
                            $('#productForm')[0].reset();
                            $('#editingId').val('');
                            $('#cancelEditBtn').hide();
                            
                            // Reload products
                            loadProducts();
                            
                            // Show success message (optional)
                            alert(response.message || 'Product saved successfully!');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        }
                        alert(errorMsg);
                    }
                });
            });

            // Cancel edit
            $('#cancelEditBtn').on('click', function() {
                $('#productForm')[0].reset();
                $('#editingId').val('');
                $(this).hide();
            });

            // Edit product (delegated event for dynamically added buttons)
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                
                $('#product_name').val(row.find('td:eq(0)').text());
                $('#quantity').val(row.find('td:eq(1)').text());
                $('#price').val(row.find('td:eq(2)').text());
                $('#editingId').val(id);
                $('#cancelEditBtn').show();
                
                // Scroll to form
                $('html, body').animate({
                    scrollTop: $('#productForm').offset().top - 100
                }, 500);
            });

            // Load products function
            function loadProducts() {
                $.ajax({
                    url: '{{ route("products.getAll") }}',
                    method: 'GET',
                    success: function(response) {
                        const tbody = $('#productsTableBody');
                        tbody.empty();

                        if (response.products.length === 0) {
                            tbody.append('<tr><td colspan="6" class="text-center">No products found. Add your first product above.</td></tr>');
                            $('#sumTotalRow').hide();
                        } else {
                            response.products.forEach(function(product) {
                                const row = `
                                    <tr>
                                        <td>${escapeHtml(product.product_name)}</td>
                                        <td>${product.quantity}</td>
                                        <td>${formatPrice(product.price)}</td>
                                        <td>${product.datetime_submitted}</td>
                                        <td>${formatPrice(product.total_value)}</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning btn-edit" data-id="${product.id}">Edit</button>
                                        </td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });

                            // Show sum total
                            $('#sumTotalValue').text(formatPrice(response.sum_total));
                            $('#sumTotalRow').show();
                        }
                    },
                    error: function() {
                        $('#productsTableBody').html('<tr><td colspan="6" class="text-center text-danger">Error loading products. Please refresh the page.</td></tr>');
                    }
                });
            }

            // Helper functions
            function formatPrice(price) {
                return parseFloat(price).toFixed(2);
            }

            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
            }
        });
    </script>
</body>
</html>

