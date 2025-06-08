// Auth state management
const AUTH_TOKEN_KEY = 'auth_token';
const USER_KEY = 'user_data';

// Store authentication data
function storeAuthData(token, userData) {
    localStorage.setItem(AUTH_TOKEN_KEY, token);
    localStorage.setItem(USER_KEY, JSON.stringify(userData));
}

// Get stored token
function getStoredToken() {
    return localStorage.getItem(AUTH_TOKEN_KEY);
}

// Get stored user data
function getStoredUser() {
    const userData = localStorage.getItem(USER_KEY);
    return userData ? JSON.parse(userData) : null;
}

// Clear auth data (for logout)
function clearAuthData() {
    localStorage.removeItem(AUTH_TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
}

// Check if user is logged in
function isLoggedIn() {
    return !!getStoredToken();
}

// Login function
async function login(email, password) {
    try {
        const loginUrl = window.Constants.getApiUrl(window.Constants.API.LOGIN);
        const response = await fetch(loginUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();

        if (data.success) {
            storeAuthData(data.data.token, data.data.user);
            return { success: true };
        } else {
            return { success: false, error: data.error };
        }
    } catch (error) {
        return { success: false, error: 'Network error occurred' };
    }
}

// Register function
async function register(email, password) {
    try {
        const registerUrl = window.Constants.getApiUrl(window.Constants.API.REGISTER);
        const response = await fetch(registerUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();

        if (data.success) {
            return { success: true };
        } else {
            return { success: false, error: data.error };
        }
    } catch (error) {
        return { success: false, error: 'Network error occurred' };
    }
}

// Logout function
function logout() {
    clearAuthData();
    if (window.location.hash !== '#page1') {
        window.location.hash = 'page1';
    }
} 