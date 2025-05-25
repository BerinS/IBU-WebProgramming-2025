// Prevent redeclaration by checking if ProductService already exists
var ProductService = window.ProductService || {
    init: function() {
        if (typeof RestClient === 'undefined' || typeof Utils === 'undefined' || typeof Constants === 'undefined') {
            console.error('Required dependencies not loaded');
            return;
        }
        
        // Load products if we're on the dashboard page
        if (window.location.hash === '#dashboard') {
            this.loadProducts();
        }

        // Listen for view changes
        $(document).on('spapp.view.after', function(e, view) {
            if (view === '#dashboard') {
                ProductService.loadProducts();
            }
        });
    },

    loadProducts: function() {
        console.log('Loading products...');
        RestClient.get(Constants.API.PRODUCTS, null,
            function(response) {
                console.log('Products response:', response);
                // The response is directly the array of products
                if (Array.isArray(response)) {
                    console.log('Response is an array, displaying products...');
                    ProductService.displayProducts(response);
                } else if (response && response.data) {
                    // Fallback for if the response format changes in the future
                    console.log('Response has data property, displaying products...');
                    ProductService.displayProducts(response.data);
                } else {
                    console.error('Invalid response format:', response);
                    Utils.showError("Invalid response format from server");
                }
            },
            function(error) {
                Utils.showError("Failed to load products");
                console.error('Error loading products:', error);
            }
        );
    },

    displayProducts: function(products) {
        console.log('Displaying products:', products);
        const productsList = $('.list-group');
        console.log('Found products list element:', productsList.length > 0);
        productsList.empty(); // Clear existing items

        if (!Array.isArray(products) || products.length === 0) {
            console.log('No products to display');
            productsList.html('<div class="text-center py-3">No products found</div>');
            return;
        }

        products.forEach(function(product) {
            const productItem = `
                <div class="list-group-item" data-product-id="${product.id}">
                    <div class="row align-items-center">
                        <div class="col-2">
                            <img src="${product.image_url}" alt="${product.name}" class="img-fluid" style="max-height: 100px; object-fit: contain;">
                        </div>
                        <div class="col-6">
                            <h5 class="mb-1">${product.name}</h5>
                            <p class="mb-1"><strong>Brand:</strong> ${product.brand}</p>
                            <p class="mb-1"><strong>Price:</strong> $${product.price}</p>
                            <p class="mb-1"><strong>Stock:</strong> ${product.stock_quantity}</p>
                        </div>
                        <div class="col-4 text-end">
                            <button class="btn btn-sm btn-outline-primary edit-product me-2" title="Edit product">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-product" title="Delete product">
                                <i class="bi bi-x-circle"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>`;
            productsList.append(productItem);
        });

        // Add event listeners for edit and delete
        $('.edit-product').click(function(e) {
            e.stopPropagation();
            const productId = $(this).closest('.list-group-item').data('product-id');
            ProductService.editProduct(productId);
        });

        $('.delete-product').click(function(e) {
            e.stopPropagation();
            const productId = $(this).closest('.list-group-item').data('product-id');
            ProductService.deleteProduct(productId);
        });
    },

    editProduct: function(productId) {
        // TODO: Implement edit functionality
        console.log('Edit product:', productId);
    },

    deleteProduct: function(productId) {
        if (confirm('Are you sure you want to delete this product?')) {
            RestClient.delete(Constants.API.PRODUCTS + '/' + productId, null,
                function(response) {
                    Utils.showSuccess('Product deleted successfully');
                    ProductService.loadProducts(); // Reload the list
                },
                function(error) {
                    Utils.showError('Failed to delete product');
                    console.error('Error deleting product:', error);
                }
            );
        }
    },

    // New methods for adding products
    loadCategories: function(callback) {
        // Check if Constants is loaded
        if (typeof Constants === 'undefined' || !Constants.API || !Constants.API.CATEGORIES) {
            console.error('Constants not properly loaded');
            Utils.showError("System configuration error");
            return;
        }

        RestClient.get(Constants.API.CATEGORIES, null,
            function(response) {
                if (Array.isArray(response)) {
                    callback(response);
                } else if (response && response.data) {
                    callback(response.data);
                } else {
                    console.error('Invalid categories response format:', response);
                    Utils.showError("Invalid categories response format from server");
                }
            },
            function(error) {
                Utils.showError("Failed to load categories");
                console.error('Error loading categories:', error);
            }
        );
    },

    addProduct: function(productData) {
        RestClient.post(Constants.API.PRODUCTS, productData,
            function(response) {
                Utils.showSuccess('Product added successfully');
                $('#productModal').modal('hide');
                ProductService.loadProducts(); // Reload the list
                $('#add-product-form')[0].reset();
            },
            function(error) {
                const errorMessage = error.responseJSON?.error || 'Failed to add product';
                $('#add-product-error')
                    .text(errorMessage)
                    .show();
                console.error('Error adding product:', error);
            }
        );
    }
};

// Make ProductService available globally
window.ProductService = ProductService;

// Initialize the service when document is ready
$(document).ready(function() {
    ProductService.init();
}); 