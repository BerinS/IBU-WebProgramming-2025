if (typeof window.Constants === 'undefined') {
    window.Constants = {
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
            CATEGORIES: "categories"
        },

        // Local Storage Keys
        STORAGE: {
            USER_TOKEN: "user_token",
            USER_DATA: "user_data"
        }
    };
}
 