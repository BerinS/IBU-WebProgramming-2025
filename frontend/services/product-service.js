// Prevent redeclaration by checking if ProductService already exists
var ProductService = window.ProductService || {
    // Global variables for shop functionality
    currentProducts: [],
    filteredProducts: [],
    searchTimeout: null,

    init: function() {
        if (typeof RestClient === 'undefined' || typeof Utils === 'undefined' || typeof Constants === 'undefined') {
            console.error('Required dependencies not loaded');
            return;
        }
        
        // Load products based on current view
        this.checkCurrentView();

        // Listen for view changes (SPA navigation)
        $(document).on('spapp.view.after', function(e, view) {
            if (view === '#dashboard') {
                ProductService.loadProducts();
            } else if (view === '#shop') {
                ProductService.loadShopPage();
            } else if (view === '#page1') {
                ProductService.loadFrontPage();
            } else if (view === '#product') {
                ProductService.loadProductPage();
            }
        });

        // Handle product links with product IDs
        $(document).on('click', 'a[data-product-id]', function(e) {
            e.preventDefault();
            const productId = $(this).data('product-id');
            ProductService.navigateToProduct(productId);
        });

        // Handle navigation to shop page (for navbar clicks)
        $(document).on('click', 'a[href="#shop"]', function(e) {
            e.preventDefault();
            ProductService.navigateToShop();
        });

        // Handle navigation to front page (for navbar/logo clicks)
        $(document).on('click', 'a[href="#page1"], a[href=""], .navbar-brand', function(e) {
            e.preventDefault();
            ProductService.navigateToFrontPage();
        });
    },

    checkCurrentView: function() {
        const currentHash = window.location.hash;
        if (currentHash === '#dashboard') {
            this.loadProducts();
        } else if (currentHash === '#shop') {
            this.loadShopPage();
        } else if (currentHash === '#page1' || currentHash === '') {
            // Wait for page to be fully loaded before making API calls on front page
            if (document.readyState === 'complete') {
                this.loadFrontPage();
            } else {
                window.addEventListener('load', function() {
                    ProductService.loadFrontPage();
                });
            }
        } else if (currentHash.startsWith('#product')) {
            this.loadProductPage();
        }
    },

    // SHOP PAGE FUNCTIONALITY
    navigateToShop: function() {
        // Pre-load shop products via AJAX
        this.publicApiCall('products', 
            function(response) {
                const products = ProductService.extractProductsFromResponse(response);
                if (products) {
                    // Cache the shop products
                    sessionStorage.setItem('cachedShopProducts', JSON.stringify(products));
                }
                
                // Navigate to shop page
                window.location.hash = '#shop';
                
                // Force immediate loading after navigation
                setTimeout(function() {
                    ProductService.loadShopPage();
                }, 100);
            },
            function(error) {
                console.error('Error pre-loading shop products:', error);
                
                // Navigate anyway, will fallback to regular loading
                window.location.hash = '#shop';
                setTimeout(function() {
                    ProductService.loadShopPage();
                }, 100);
            }
        );
    },

    loadShopPage: function() {
        const cachedProducts = sessionStorage.getItem('cachedShopProducts');
        
        // If we have cached products, use them immediately
        if (cachedProducts) {
            try {
                const products = JSON.parse(cachedProducts);
                
                this.currentProducts = products;
                this.filteredProducts = products;
                this.displayShopProducts(products);
                
                // Set up event listeners
                setTimeout(() => {
                    this.setupShopEventListeners();
                }, 100);
                
                // Clear cached data after use
                sessionStorage.removeItem('cachedShopProducts');
                return;
            } catch (e) {
                console.error('Error parsing cached shop products:', e);
                // Fall back to regular loading
            }
        }
        
        // Fallback: Regular shop loading
        this.loadShopProducts();
        setTimeout(() => {
            this.setupShopEventListeners();
        }, 100);
    },

    loadShopProducts: function() {
        this.showLoading(true);
        
        this.publicApiCall('products', 
            function(response) {
                const products = ProductService.extractProductsFromResponse(response);
                if (products) {
                    ProductService.currentProducts = products;
                    ProductService.filteredProducts = products;
                    ProductService.displayShopProducts(products);
                } else {
                    ProductService.showError("Invalid response format from server");
                }
            },
            function(error) {
                ProductService.showError("Failed to load products");
                console.error('Error loading shop products:', error);
            }
        );
    },

    // Public API method for endpoints that don't require authentication
    publicApiCall: function(endpoint, callback, error_callback) {
        const url = window.Constants.getApiUrl(endpoint);
        
        $.ajax({
            url: url,
            type: 'GET',
            contentType: 'application/json',
            success: function(response) {
                if (callback) callback(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('API Error:', {
                    status: jqXHR.status,
                    statusText: textStatus,
                    error: errorThrown,
                    response: jqXHR.responseText
                });
                
                if (error_callback) {
                    error_callback(jqXHR);
                } else {
                    const message = jqXHR.responseJSON?.message || jqXHR.responseJSON?.error || 'An error occurred';
                    console.error('Public API Error:', message);
                }
            }
        });
    },

    displayShopProducts: function(products) {
        const productGrid = $('#productGrid');
        const noProductsMessage = $('#noProductsMessage');
        
        this.showLoading(false);
        
        if (!productGrid.length) {
            console.error('Product grid element not found');
            return;
        }
        
        if (!Array.isArray(products) || products.length === 0) {
            productGrid.empty();
            noProductsMessage.show();
            return;
        }

        noProductsMessage.hide();
        const productsHTML = products.map(product => this.createProductCard(product, 'shop')).join('');
        productGrid.html(productsHTML);
    },

    createProductCard: function(product, type) {
        // Use relative path that works in both local and production
        const imageUrl = product.image_url || './images/product_watches/default-watch.png';
        const formattedPrice = this.formatPrice(product.price);
        
        // Different column classes based on type
        const colClass = type === 'featured' ? 'col-12 col-md-4 col-lg-4 mb-5 mb-md-0' : 'col-12 col-md-4 col-lg-3 mb-5';
        
        return `
            <div class="${colClass}">
                <a class="product-item" href="#product" data-product-id="${product.id}">
                    <img src="${imageUrl}" class="img-fluid product-thumbnail" alt="${product.name}">
                    <h3 class="product-title">${product.name}</h3>
                    <strong class="product-price">${formattedPrice}</strong>
                    <span class="icon-cross">
                                                    <img src="./images/cross.svg" class="img-fluid">
                    </span>
                </a>
            </div>
        `;
    },

    formatPrice: function(price) {
        const numPrice = parseFloat(price);
        return `$${numPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    },

    // Search functionality
    handleSearch: function(searchTerm) {
        const term = searchTerm.toLowerCase().trim();
        
        if (term === '') {
            this.filteredProducts = this.currentProducts;
        } else {
            this.filteredProducts = this.currentProducts.filter(product => 
                product.name.toLowerCase().includes(term) ||
                product.brand.toLowerCase().includes(term) ||
                (product.description && product.description.toLowerCase().includes(term))
            );
        }
        
        this.applyFilters();
    },

    // Sorting functionality
    handleSort: function(sortType) {
        let sortedProducts = [...this.filteredProducts];
        
        switch(sortType) {
            case 'Price: high to low':
                sortedProducts.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
                break;
            case 'Price: low to high':
                sortedProducts.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
                break;
            case 'A to Z':
                sortedProducts.sort((a, b) => a.name.localeCompare(b.name));
                break;
            case 'Z to A':
                sortedProducts.sort((a, b) => b.name.localeCompare(a.name));
                break;
            case 'Best selling':
                sortedProducts.sort((a, b) => parseFloat(b.stock_quantity || 0) - parseFloat(a.stock_quantity || 0));
                break;
        }
        
        this.displayShopProducts(sortedProducts);
    },

    // Filter functionality
    applyFilters: function() {
        let products = [...this.filteredProducts];
        
        // Get active brand filters
        const activeBrands = this.getActiveCheckboxValues('brand');
        if (activeBrands.length > 0) {
            products = products.filter(product => 
                activeBrands.some(brand => 
                    product.brand.toLowerCase().includes(brand.toLowerCase())
                )
            );
        }
        
        // Get active gender filters
        const activeGenders = this.getActiveCheckboxValues('gender');
        if (activeGenders.length > 0) {
            products = products.filter(product => 
                activeGenders.some(gender => 
                    product.gender.toLowerCase() === gender.toLowerCase()
                )
            );
        }
        
        // Get active price filters
        const activePriceRanges = this.getActivePriceRanges();
        if (activePriceRanges.length > 0) {
            products = products.filter(product => {
                const price = parseFloat(product.price);
                return activePriceRanges.some(range => 
                    price >= range.min && price <= range.max
                );
            });
        }
        
        this.displayShopProducts(products);
    },

    getActiveCheckboxValues: function(filterType) {
        const checkboxes = $(`input[id*="${filterType}"]:checked`);
        return checkboxes.map(function() {
            const label = $(`label[for="${this.id}"]`);
            return label.length ? label.text().trim() : '';
        }).get().filter(value => value !== '');
    },

    getActivePriceRanges: function() {
        const priceCheckboxes = $('input[id*="price"]:checked');
        const ranges = [];
        
        priceCheckboxes.each(function() {
            const label = $(`label[for="${this.id}"]`);
            if (label.length) {
                const text = label.text().trim();
                const range = ProductService.parsePriceRange(text);
                if (range) ranges.push(range);
            }
        });
        
        return ranges;
    },

    parsePriceRange: function(text) {
        if (text.includes('< $250')) {
            return { min: 0, max: 250 };
        } else if (text.includes('$250 - $500')) {
            return { min: 250, max: 500 };
        } else if (text.includes('$500 - $1000')) {
            return { min: 500, max: 1000 };
        } else if (text.includes('$1000 - $2000')) {
            return { min: 1000, max: 2000 };
        } else if (text.includes('$2000 - $5000')) {
            return { min: 2000, max: 5000 };
        } else if (text.includes('> $5000')) {
            return { min: 5000, max: Infinity };
        }
        return null;
    },

    showLoading: function(show) {
        const loadingIndicator = $('#loadingIndicator');
        const productGrid = $('#productGrid');
        
        if (show) {
            if (loadingIndicator.length) loadingIndicator.show();
            if (productGrid.length) productGrid.hide();
        } else {
            if (loadingIndicator.length) loadingIndicator.hide();
            if (productGrid.length) productGrid.show();
        }
    },

    showError: function(message) {
        const productGrid = $('#productGrid');
        if (productGrid.length) {
            productGrid.html(`
                <div class="col-12 text-center">
                    <div class="alert alert-danger" role="alert">
                        ${message}
                    </div>
                </div>
            `);
        }
    },

    setupShopEventListeners: function() {
        // Remove existing shop event listeners to prevent duplicates
        $('.search_bar_input, .dropdown-menu a, .form-check-input, #toggleSidebar, #closeSidebar')
            .off('.shop');

        // Search with debouncing
        $('.search_bar_input').on('input.shop', function() {
            clearTimeout(ProductService.searchTimeout);
            ProductService.searchTimeout = setTimeout(function() {
                ProductService.handleSearch($('.search_bar_input').val());
            }, 300);
        });

        // Sort dropdown
        $('.dropdown-menu a').on('click.shop', function(e) {
            e.preventDefault();
            ProductService.handleSort($(this).text().trim());
        });

        // Filter checkboxes
        $('.form-check-input').on('change.shop', function() {
            ProductService.applyFilters();
        });

        // Mobile sidebar toggle
        $('#toggleSidebar').on('click.shop', function() {
            $('#sidebarFilter').toggleClass('active');
        });

        $('#closeSidebar').on('click.shop', function() {
            $('#sidebarFilter').removeClass('active');
        });
    },

    // DASHBOARD FUNCTIONALITY
    loadProducts: function() {
        RestClient.get(Constants.API.PRODUCTS, null,
            function(response) {
                const products = ProductService.extractProductsFromResponse(response);
                if (products) {
                    ProductService.displayProducts(products);
                } else {
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
        const productsList = $('.list-group');
        productsList.empty(); // Clear existing items

        if (!Array.isArray(products) || products.length === 0) {
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
        if (typeof RestClient === 'undefined') {
            console.error('RestClient is not loaded');
            return;
        }
        
        if (!Constants.API || !Constants.API.PRODUCTS) {
            console.error('Constants.API.PRODUCTS is not properly initialized');
            return;
        }
        
        const endpoint = Constants.API.PRODUCTS + '/' + productId;
        
        RestClient.get(endpoint, null,
            function(response) {
                $('#productModalLabel').text('Edit Product');
                
                $('#add-product-form').data('product-id', productId);
                
                $('.auth-button').text('Update Product');
                
                $('#product-name').val(response.name);
                $('#product-brand').val(response.brand);
                $('#product-stock').val(response.stock_quantity);
                $('#product-price').val(response.price);
                $('#product-description').val(response.description);
                $('#product-image').val(response.image_url);
                $('#product-gender').val(response.gender);
                
                ProductService.loadCategories(function(categories) {
                    const select = $('#product-category');
                    select.empty();
                    categories.forEach(category => {
                        select.append(`<option value="${category.id}">${category.name}</option>`);
                    });
                    select.val(response.category_id);
                });
                
                const modal = $('#productModal');
                if (modal.length) {
                    modal.modal('show');
                } else {
                    console.error('Modal element not found');
                }
            },
            function(error) {
                console.error('Error in GET request:', error);
                console.error('Error status:', error.status);
                console.error('Error response:', error.responseText);
                Utils.showError('Failed to load product details');
            }
        );
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
    },

    updateProduct: function(productId, productData) {
        RestClient.put(Constants.API.PRODUCTS + '/' + productId, productData,
            function(response) {
                Utils.showSuccess('Product updated successfully');
                $('#productModal').modal('hide');
                ProductService.loadProducts(); // Reload the list
                $('#add-product-form')[0].reset();
                // Reset modal to add mode
                $('#productModalLabel').text('Add New Product');
                $('.auth-button').text('Add Product');
                $('#add-product-form').removeData('product-id');
            },
            function(error) {
                const errorMessage = error.responseJSON?.error || 'Failed to update product';
                $('#add-product-error')
                    .text(errorMessage)
                    .show();
                console.error('Error updating product:', error);
            }
        );
    },

    // FRONT PAGE FUNCTIONALITY
    loadFeaturedProducts: function() {
        this.publicApiCall('products', 
            function(response) {
                const products = ProductService.extractProductsFromResponse(response);
                if (products) {
                    ProductService.displayFeaturedProducts(ProductService.getRandomProducts(products, 3));
                }
                // Silently fail for featured products - front page should still work
            },
            function(error) {
                console.error('Error loading featured products:', error);
                // Silently fail for featured products - front page should still work
            }
        );
    },

    getRandomProducts: function(products, count) {
        if (!Array.isArray(products) || products.length === 0) {
            return [];
        }
        
        // Use Fisher-Yates shuffle for better randomization
        const shuffled = products.slice();
        for (let i = shuffled.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        }
        
        return shuffled.slice(0, Math.min(count, products.length));
    },

    displayFeaturedProducts: function(products) {
        const container = $('#featuredProductsRow');
        
        if (!container.length) {
            return; // Not on front page
        }
        
        if (!Array.isArray(products) || products.length === 0) {
            container.html('<div class="col-12 text-center"><p>Featured products coming soon...</p></div>');
            return;
        }

        const productsHTML = products.map(product => this.createProductCard(product, 'featured')).join('');
        container.html(productsHTML);
    },

    // UTILITY METHODS
    extractProductsFromResponse: function(response) {
        if (Array.isArray(response)) {
            return response;
        } else if (response && response.data && Array.isArray(response.data)) {
            return response.data;
        }
        console.error('Invalid response format:', response);
        return null;
    },

    // Compatibility methods for backward compatibility
    createShopProductCard: function(product) {
        return this.createProductCard(product, 'shop');
    },

    createFeaturedProductCard: function(product) {
        return this.createProductCard(product, 'featured');
    },

    // PRODUCT PAGE FUNCTIONALITY
    navigateToProduct: function(productId) {
        // Show loading state if user is on shop page
        if (window.location.hash === '#shop') {
            this.showLoading(true);
        }

        // Fetch product data first via AJAX
        this.publicApiCall(`products/${productId}`, 
            function(response) {
                // Store both the product ID and the fetched data
                sessionStorage.setItem('currentProductId', productId);
                sessionStorage.setItem('currentProductData', JSON.stringify(response));
                
                // Hide loading state
                ProductService.showLoading(false);
                
                // Navigate to product page and immediately load the data
                window.location.hash = '#product';
                
                // Force immediate loading after navigation
                setTimeout(function() {
                    ProductService.loadProductPage();
                }, 100);
            },
            function(error) {
                console.error('Error loading product for navigation:', error);
                ProductService.showLoading(false);
                
                // Navigate anyway but with error handling
                sessionStorage.setItem('currentProductId', productId);
                sessionStorage.removeItem('currentProductData'); // Clear any old data
                window.location.hash = '#product';
                
                // Still try to load (will fallback to API call)
                setTimeout(function() {
                    ProductService.loadProductPage();
                }, 100);
            }
        );
    },

    loadProductPage: function() {
        const productId = sessionStorage.getItem('currentProductId');
        const cachedData = sessionStorage.getItem('currentProductData');
        
        if (!productId) {
            console.error('No product ID found for product page');
            return;
        }

        // If we have pre-loaded data from navigation, use it immediately
        if (cachedData) {
            try {
                const productData = JSON.parse(cachedData);
                this.populateProductPage(productData);
                // Clear the cached data after use
                sessionStorage.removeItem('currentProductData');
                return;
            } catch (e) {
                console.error('Error parsing cached product data:', e);
                // Fall back to loading from API
            }
        }

        // Fallback: Load from API if no cached data (e.g., direct URL access)
        this.loadSingleProduct(productId);
    },

    loadSingleProduct: function(productId) {
        this.publicApiCall(`products/${productId}`, 
            function(response) {
                ProductService.populateProductPage(response);
            },
            function(error) {
                console.error('Error loading product:', error);
                ProductService.showProductError('Failed to load product details');
            }
        );
    },

    populateProductPage: function(product) {
        if (!product) {
            this.showProductError('Product not found');
            return;
        }

        // Ensure we're on the product page before trying to populate
        if (!$('.product_page').length || !$('.product_details').length) {
            // Retry after a short delay
            setTimeout(() => {
                this.populateProductPage(product);
            }, 200);
            return;
        }

        // Update main product image
        const mainImage = $('#mainImage');
        if (mainImage.length && product.image_url) {
            mainImage.attr('src', product.image_url).attr('alt', product.name);
        }

        // Update thumbnails to use the main product image
        const thumbnails = $('.thumbnail');
        thumbnails.each(function() {
            if (product.image_url) {
                $(this).attr('src', product.image_url).attr('alt', product.name);
            }
        });

        // Update product title
        $('.product_details h2').text(product.name || 'Product Name');

        // Update brand as SKU (target the text-muted paragraph specifically)
        $('.product_details p.text-muted.mb-4').text(`Brand: ${product.brand || 'Unknown'}`);

        // Update price
        const priceContainer = $('.product_details .mb-3').eq(1);
        const formattedPrice = this.formatPrice(product.price);
        priceContainer.html(`<span class="h4 me-2">${formattedPrice}</span>`);

        // Update description (target the p.mb-4 that is NOT text-muted)
        $('.product_details p.mb-4:not(.text-muted)').text(product.description || 'No description available.');

        // Update key features based on available data
        const featuresContainer = $('.product_details .mt-4 ul');
        if (featuresContainer.length) {
            const features = this.generateProductFeatures(product);
            featuresContainer.html(features);
        }

        // Store product data for add to cart functionality
        $('#quantity').data('product-id', product.id);
        $('#quantity').data('product-price', product.price);
        $('#quantity').data('stock-quantity', product.stock_quantity);

        // Update quantity input max value based on stock
        if (product.stock_quantity) {
            $('#quantity').attr('max', product.stock_quantity);
        }
    },

    generateProductFeatures: function(product) {
        const features = [];
        
        if (product.brand) {
            features.push(`<li>Brand: ${product.brand}</li>`);
        }
        
        if (product.gender) {
            features.push(`<li>Gender: ${product.gender}</li>`);
        }
        
        if (product.stock_quantity) {
            const stockStatus = product.stock_quantity > 10 ? 'In Stock' : 
                               product.stock_quantity > 0 ? `Limited Stock (${product.stock_quantity} remaining)` : 
                               'Out of Stock';
            features.push(`<li>Availability: ${stockStatus}</li>`);
        }
        
        // Add some default features to maintain the UI structure
        if (features.length < 3) {
            features.push('<li>Premium Quality Materials</li>');
            features.push('<li>2 Year Warranty</li>');
        }
        
        return features.join('');
    },

    showProductError: function(message) {
        $('.product_details h2').text('Product Not Found');
        $('.product_details p.mb-4').text(message);
                    $('#mainImage').attr('src', './images/product_watches/default-watch.png');
            $('.thumbnail').attr('src', './images/product_watches/default-watch.png');
    },

    // FRONT PAGE FUNCTIONALITY
    navigateToFrontPage: function() {
        // Pre-load products for featured section via AJAX
        this.publicApiCall('products', 
            function(response) {
                const products = ProductService.extractProductsFromResponse(response);
                if (products) {
                    // Cache the products for featured section
                    sessionStorage.setItem('cachedFrontPageProducts', JSON.stringify(products));
                }
                
                // Navigate to front page
                window.location.hash = '#page1';
                
                // Force immediate loading after navigation
                setTimeout(function() {
                    ProductService.loadFrontPage();
                }, 100);
            },
            function(error) {
                console.error('Error pre-loading front page products:', error);
                
                // Navigate anyway, will fallback to regular loading
                window.location.hash = '#page1';
                setTimeout(function() {
                    ProductService.loadFrontPage();
                }, 100);
            }
        );
    },

    loadFrontPage: function() {
        const cachedProducts = sessionStorage.getItem('cachedFrontPageProducts');
        
        // If we have cached products, use them immediately
        if (cachedProducts) {
            try {
                const products = JSON.parse(cachedProducts);
                
                // Get 3 random products for featured section
                const featuredProducts = this.getRandomProducts(products, 3);
                this.displayFeaturedProducts(featuredProducts);
                
                // Clear cached data after use
                sessionStorage.removeItem('cachedFrontPageProducts');
                return;
            } catch (e) {
                console.error('Error parsing cached front page products:', e);
                // Fall back to regular loading
            }
        }
        
        // Fallback: Regular featured products loading with retry logic for CORS issues
        this.loadFeaturedProducts();
    },
};

// Make ProductService available globally
window.ProductService = ProductService;

// Initialize the service when document is ready
$(document).ready(function() {
    ProductService.init();
}); 