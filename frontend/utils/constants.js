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
            // Dynamically determine the config URL based on current domain
            let configUrl;
            const currentHost = window.location.hostname;
            const currentProtocol = window.location.protocol;
            
            if (currentHost === 'localhost' || currentHost === '127.0.0.1') {
                // Local development
                configUrl = 'http://localhost/IBU-WebProgramming-2025/backend/config';
            } else {
                // Production (DigitalOcean or other hosting)
                configUrl = `${currentProtocol}//${window.location.host}/backend/config`;
            }
            
            console.log('Trying to load config from:', configUrl);
            const response = await fetch(configUrl);
            
            if (response.ok) {
                const config = await response.json();
                this.updateBaseUrl(config.baseUrl);
                console.log('Configuration loaded successfully:', config);
            } else {
                throw new Error(`Config request failed with status: ${response.status}`);
            }
        } catch (error) {
            console.log('Failed to load remote config, using default configuration:', error.message);
            // Keep default localhost configuration if config endpoint fails
        }
    };
    
    // Load config immediately when constants are defined
    window.Constants.loadConfig();
}
 