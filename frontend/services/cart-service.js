// Prevent redeclaration by checking if CartService already exists
var CartService = window.CartService || {
    
    // Add product to cart
    addToCart: function(productId, quantity) {
        const user = Utils.getCurrentUser();
        
        if (!user) {
            Utils.showError('Please login to add items to cart');
            return;
        }

        if (!productId || !quantity || quantity < 1) {
            Utils.showError('Invalid product or quantity');
            return;
        }

        const data = {
            user_id: user.id,
            product_id: productId,
            quantity: parseInt(quantity)
        };

        RestClient.post(Constants.API.CART_ITEMS, data,
            function(response) {
                Utils.showSuccess('Product added to cart!');
                console.log('Item added to cart successfully:', response);
            },
            function(error) {
                console.error('Error adding to cart:', error);
                const errorMessage = error.responseJSON?.message || 'Failed to add item to cart';
                Utils.showError(errorMessage);
            }
        );
    },

    // Get user's cart
    getCart: function(callback, error_callback) {
        const user = Utils.getCurrentUser();
        
        if (!user) {
            console.log('No user logged in');
            if (error_callback) error_callback({ message: 'Not logged in' });
            return;
        }

        RestClient.get(`${Constants.API.CART}/${user.id}`, null,
            function(response) {
                console.log('Cart loaded:', response);
                if (callback) callback(response);
            },
            function(error) {
                console.error('Error loading cart:', error);
                if (error_callback) error_callback(error);
            }
        );
    },

    // Update cart item quantity
    updateQuantity: function(itemId, quantity, callback, error_callback) {
        if (!itemId || !quantity || quantity < 1) {
            Utils.showError('Invalid item or quantity');
            return;
        }

        const data = { quantity: parseInt(quantity) };

        RestClient.put(`${Constants.API.CART_ITEMS}/${itemId}`, data,
            function(response) {
                console.log('Quantity updated:', response);
                if (callback) callback(response);
            },
            function(error) {
                console.error('Error updating quantity:', error);
                const errorMessage = error.responseJSON?.message || 'Failed to update quantity';
                Utils.showError(errorMessage);
                if (error_callback) error_callback(error);
            }
        );
    },

    // Remove item from cart
    removeItem: function(itemId, callback, error_callback) {
        if (!itemId) {
            Utils.showError('Invalid item');
            return;
        }

        RestClient.delete(`${Constants.API.CART_ITEMS}/${itemId}`, null,
            function(response) {
                Utils.showSuccess('Item removed from cart');
                console.log('Item removed:', response);
                if (callback) callback(response);
            },
            function(error) {
                console.error('Error removing item:', error);
                const errorMessage = error.responseJSON?.message || 'Failed to remove item';
                Utils.showError(errorMessage);
                if (error_callback) error_callback(error);
            }
        );
    },

    // Calculate cart totals
    calculateTotals: function(cartItems) {
        if (!Array.isArray(cartItems) || cartItems.length === 0) {
            return { subtotal: 0, total: 0 };
        }

        const subtotal = cartItems.reduce((sum, item) => {
            const price = parseFloat(item.price) || 0;
            const quantity = parseInt(item.quantity) || 0;
            return sum + (price * quantity);
        }, 0);

        return {
            subtotal: subtotal,
            total: subtotal // For now, no tax or shipping
        };
    },

    // Format price for display
    formatPrice: function(price) {
        const numPrice = parseFloat(price) || 0;
        return `$${numPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    },

    // Auto-refresh cart when navigating to cart page
    setupAutoRefresh: function() {
        $(document).on('click', 'a[href="#cart"]', function() {
            setTimeout(function() {
                if (window.location.hash === '#cart') {
                    // Try page-specific function first, otherwise use service method
                    if (typeof loadCart === 'function') {
                        loadCart();
                    } else {
                        CartService.getCart();
                    }
                }
            }, 200);
        });
    }
};

// Make CartService available globally
window.CartService = CartService; 