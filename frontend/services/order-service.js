// Prevent redeclaration by checking if OrderService already exists
var OrderService = window.OrderService || {
    
    // Create order from cart
    createOrder: function(callback, error_callback) {
        const user = Utils.getCurrentUser();
        
        if (!user) {
            Utils.showError('Please login to place an order');
            if (error_callback) error_callback({ message: 'Not logged in' });
            return;
        }

        const data = {
            user_id: user.id
        };

        RestClient.post(Constants.API.ORDERS, data,
            function(response) {
                if (response.success) {
                    Utils.showSuccess('Order placed successfully!');
                    console.log('Order created successfully:', response);
                    if (callback) callback(response);
                } else {
                    Utils.showError(response.message || 'Failed to create order');
                    if (error_callback) error_callback(response);
                }
            },
            function(error) {
                console.error('Error creating order:', error);
                const errorMessage = error.responseJSON?.message || 'Failed to create order';
                Utils.showError(errorMessage);
                if (error_callback) error_callback(error);
            }
        );
    },

    // Get user's orders
    getOrders: function(callback, error_callback) {
        const user = Utils.getCurrentUser();
        
        if (!user) {
            console.log('No user logged in');
            if (error_callback) error_callback({ message: 'Not logged in' });
            return;
        }

        RestClient.get(`${Constants.API.ORDERS}/user/${user.id}`, null,
            function(response) {
                console.log('Orders loaded:', response);
                if (callback) callback(response.data || []);
            },
            function(error) {
                console.error('Error loading orders:', error);
                if (error_callback) error_callback(error);
            }
        );
    },

    // Get all orders (Admin/Employee only)
    getAllOrders: function(callback, error_callback) {
        const user = Utils.getCurrentUser();
        
        if (!user || (user.role !== Constants.ROLES.ADMIN && user.role !== Constants.ROLES.EMPLOYEE)) {
            Utils.showError('Access denied: Insufficient privileges');
            if (error_callback) error_callback({ message: 'Access denied' });
            return;
        }

        RestClient.get(Constants.API.ORDERS, null,
            function(response) {
                console.log('All orders loaded:', response);
                if (callback) callback(response.data || []);
            },
            function(error) {
                console.error('Error loading all orders:', error);
                if (error_callback) error_callback(error);
            }
        );
    },

    // Update order status (Admin/Employee only)
    updateOrderStatus: function(orderId, status, callback, error_callback) {
        const user = Utils.getCurrentUser();
        
        if (!user || (user.role !== Constants.ROLES.ADMIN && user.role !== Constants.ROLES.EMPLOYEE)) {
            Utils.showError('Access denied: Insufficient privileges');
            if (error_callback) error_callback({ message: 'Access denied' });
            return;
        }

        const data = { status: status };

        RestClient.put(`${Constants.API.ORDERS}/${orderId}/status`, data,
            function(response) {
                if (response.success) {
                    Utils.showSuccess('Order status updated successfully');
                    console.log('Order status updated:', response);
                    if (callback) callback(response);
                } else {
                    Utils.showError(response.message || 'Failed to update order status');
                    if (error_callback) error_callback(response);
                }
            },
            function(error) {
                console.error('Error updating order status:', error);
                const errorMessage = error.responseJSON?.message || 'Failed to update order status';
                Utils.showError(errorMessage);
                if (error_callback) error_callback(error);
            }
        );
    },

    // Delete order (Admin only)
    deleteOrder: function(orderId, callback, error_callback) {
        const user = Utils.getCurrentUser();
        
        if (!user || user.role !== Constants.ROLES.ADMIN) {
            Utils.showError('Access denied: Admin privileges required');
            if (error_callback) error_callback({ message: 'Access denied' });
            return;
        }

        RestClient.delete(`${Constants.API.ORDERS}/${orderId}`, null,
            function(response) {
                if (response.success) {
                    Utils.showSuccess('Order deleted successfully');
                    console.log('Order deleted:', response);
                    if (callback) callback(response);
                } else {
                    Utils.showError(response.message || 'Failed to delete order');
                    if (error_callback) error_callback(response);
                }
            },
            function(error) {
                console.error('Error deleting order:', error);
                const errorMessage = error.responseJSON?.message || 'Failed to delete order';
                Utils.showError(errorMessage);
                if (error_callback) error_callback(error);
            }
        );
    },

    // Format order status for display
    formatStatus: function(status) {
        const statusMap = {
            'pending': 'Pending',
            'shipped': 'Shipped'
        };
        return statusMap[status] || status;
    },

    // Format order date for display
    formatDate: function(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    // Format price for display (reuse from CartService)
    formatPrice: function(price) {
        const numPrice = parseFloat(price) || 0;
        return `$${numPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }
};

// Make OrderService available globally
window.OrderService = OrderService; 