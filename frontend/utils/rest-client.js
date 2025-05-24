// Prevent redeclaration by checking if RestClient already exists
var RestClient = window.RestClient || {
    // Generic request function
    request: function(url, method, data, callback, error_callback) {
        console.log('Making request to:', Constants.PROJECT_BASE_URL + url);
        console.log('Method:', method);
        console.log('Data:', data);
        
        $.ajax({
            url: Constants.PROJECT_BASE_URL + url,
            type: method,
            data: method === 'GET' ? data : JSON.stringify(data),
            contentType: 'application/json',
            beforeSend: function(xhr) {
                // Add Authorization header if user is logged in
                const token = localStorage.getItem(Constants.STORAGE.USER_TOKEN);
                if (token) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                }
            },
            success: function(response) {
                console.log('Success response:', response);
                if (callback) callback(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('Error status:', jqXHR.status);
                console.log('Error response:', jqXHR.responseJSON);
                console.log('Error thrown:', errorThrown);
                
                // Handle different types of errors
                if (jqXHR.status === 401) {
                    // Just log the unauthorized error
                    console.error('Unauthorized: Invalid credentials');
                    if (error_callback) error_callback(jqXHR);
                } else if (jqXHR.status === 403) {
                    // Forbidden - show permission denied message
                    Utils.showError('Permission denied');
                    if (error_callback) error_callback(jqXHR);
                } else if (error_callback) {
                    error_callback(jqXHR);
                } else {
                    // Show error message from server or default message
                    const message = jqXHR.responseJSON?.message || jqXHR.responseJSON?.error || 'An error occurred';
                    Utils.showError(message);
                }
            }
        });
    },

    // Authentication methods
    auth: {
        login: function(email, password, callback, error_callback) {
            RestClient.post(Constants.API.LOGIN, { email, password }, function(response) {
                console.log('Login response:', response);
                
                // Check if response has the expected structure
                if (!response || !response.data || !response.data.user) {
                    console.error('Invalid response structure:', response);
                    Utils.showError('Invalid server response');
                    if (error_callback) error_callback({ responseJSON: { error: 'Invalid server response' } });
                    return;
                }

                try {
                    // Store token
                    localStorage.setItem(Constants.STORAGE.USER_TOKEN, response.data.token);
                    
                    // Store complete user data
                    const userData = {
                        id: response.data.user.id,
                        email: response.data.user.email,
                        role: response.data.user.role,
                        first_name: response.data.user.first_name,
                        last_name: response.data.user.last_name,
                        permissions: response.data.user.permissions
                    };
                    console.log('Storing user data:', userData);
                    localStorage.setItem(Constants.STORAGE.USER_DATA, JSON.stringify(userData));
                    
                    if (callback) callback(response);
                } catch (e) {
                    console.error('Error processing login response:', e);
                    Utils.showError('Error processing login response');
                    if (error_callback) error_callback({ responseJSON: { error: 'Error processing login response' } });
                }
            }, error_callback);
        },

        register: function(userData, callback, error_callback) {
            RestClient.post(Constants.API.REGISTER, userData, callback, error_callback);
        }
    },

    // HTTP method wrappers
    get: function(url, params, callback, error_callback) {
        RestClient.request(url, 'GET', params, callback, error_callback);
    },

    post: function(url, data, callback, error_callback) {
        RestClient.request(url, 'POST', data, callback, error_callback);
    },

    put: function(url, data, callback, error_callback) {
        RestClient.request(url, 'PUT', data, callback, error_callback);
    },

    delete: function(url, data, callback, error_callback) {
        RestClient.request(url, 'DELETE', data, callback, error_callback);
    },

    patch: function (url, data, callback, error_callback) {
      RestClient.request(url, "PATCH", data, callback, error_callback);
    },
};

// Make RestClient available globally
window.RestClient = RestClient;
 