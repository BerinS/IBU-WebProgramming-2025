if (typeof window.Constants === 'undefined') {
    window.Constants = {
        // Default base URL for immediate use - will be updated dynamically
        PROJECT_BASE_URL: "http://localhost/IBU-WebProgramming-2025/backend/",
        
        // User Roles
        ROLES: {
            ADMIN: "admin",
            EMPLOYEE: "employee",
            CUSTOMER: "customer"
        },

        // API Endpoints
        API: {
            LOGIN: "auth/login",
            REGISTER: "auth/register",
            PRODUCTS: "products",
            CART: "cart",
            CART_ITEMS: "cart/items",
            ORDERS: "orders",
            USER_PROFILE: "user/profile",
            USERS: "user/all",
            CATEGORIES: "categories",
            CONFIG: "config"
        },

        // Local Storage Keys
        STORAGE: {
            USER_TOKEN: "user_token",
            USER_DATA: "user_data"
        },
        
        // Method to update base URL dynamically
        updateBaseUrl: function(newBaseUrl) {
            this.PROJECT_BASE_URL = newBaseUrl.endsWith('/') ? newBaseUrl : newBaseUrl + '/';
        },
        
        // Method to get full API URL
        getApiUrl: function(endpoint) {
            return this.PROJECT_BASE_URL + endpoint;
        }
    };

    // Auto-load configuration on page load
    window.Constants.loadConfig = async function() {
        try {
            // Try to load config from the current domain first
            const response = await fetch(this.getApiUrl(this.API.CONFIG));
            if (response.ok) {
                const config = await response.json();
                this.updateBaseUrl(config.baseUrl);
                console.log('Configuration loaded:', config);
            }
        } catch (error) {
            console.log('Using default configuration (local development)');
            // Keep default localhost configuration if config endpoint fails
        }
    };
    
    // Load config immediately when constants are defined
    window.Constants.loadConfig();
}
 