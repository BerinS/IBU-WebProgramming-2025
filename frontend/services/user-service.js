// Prevent redeclaration by checking if UserService already exists
var UserService = window.UserService || {
    init: function() {
        // Check if required dependencies are loaded
        if (typeof Utils === 'undefined' || typeof Constants === 'undefined' || typeof RestClient === 'undefined') {
            console.error('Required dependencies not loaded');
            return;
        }

        // Check if user is logged in
        const user = Utils.getCurrentUser();
        
        // Handle register/login link visibility
        const registerLoginLink = $('a[href="#register_login"]').parent();
        if (user) {
            // Change link to profile when logged in
            registerLoginLink.find('a').attr('href', '#profile');
        } else {
            // Keep as register_login when not logged in
            registerLoginLink.find('a').attr('href', '#register_login');
        }

        // Handle dashboard visibility
        const dashboardLink = $('#dashboard_link').parent();
        if (user && (user.role === Constants.ROLES.ADMIN || user.role === Constants.ROLES.EMPLOYEE)) {
            dashboardLink.show();
        } else {
            dashboardLink.hide();
        }

        // Redirect from profile to login if not authenticated
        if (window.location.hash === '#profile' && !user) {
            window.location.hash = 'register_login';
        }

        // Redirect from register/login to profile if already authenticated
        if (window.location.hash === '#register_login' && user) {
            window.location.hash = 'profile';
        }

        // Initialize login form validation
        $("#login-form").validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minlength: 6
                }
            },
            messages: {
                email: {
                    required: "Please enter your email",
                    email: "Please enter a valid email address"
                },
                password: {
                    required: "Please enter your password",
                    minlength: "Password must be at least 6 characters long"
                }
            },
            submitHandler: function(form) {
                var entity = Object.fromEntries(new FormData(form).entries());
                UserService.login(entity);
            }
        });

        // Initialize registration form validation
        $("#register-form").validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minlength: 6
                },
                confirm_password: {
                    required: true,
                    minlength: 6,
                    equalTo: "#password"
                }
            },
            messages: {
                email: {
                    required: "Please enter your email",
                    email: "Please enter a valid email address"
                },
                password: {
                    required: "Please enter your password",
                    minlength: "Password must be at least 6 characters long"
                },
                confirm_password: {
                    required: "Please confirm your password",
                    minlength: "Password must be at least 6 characters long",
                    equalTo: "Passwords do not match"
                }
            },
            submitHandler: function(form) {
                var entity = Object.fromEntries(new FormData(form).entries());
                UserService.register(entity);
            }
        });
    },

    login: function(entity) {
        // Hide any previous error messages
        $('#login-error').hide();
        
        // Clear any existing user data first
        localStorage.removeItem(Constants.STORAGE.USER_TOKEN);
        localStorage.removeItem(Constants.STORAGE.USER_DATA);
        
        console.log('Login attempt with email:', entity.email);
        
        RestClient.auth.login(entity.email, entity.password, 
            function(response) {
                console.log('Login successful!');
                
                // Store user data in localStorage
                const userData = {
                    id: response.data.user.id,
                    email: response.data.user.email,
                    role: response.data.user.role,
                    first_name: response.data.user.first_name,
                    last_name: response.data.user.last_name,
                    permissions: response.data.user.permissions
                };
                
                // Clear any existing data first
                localStorage.removeItem(Constants.STORAGE.USER_TOKEN);
                localStorage.removeItem(Constants.STORAGE.USER_DATA);
                
                // Store new data
                localStorage.setItem(Constants.STORAGE.USER_TOKEN, response.data.token);
                localStorage.setItem(Constants.STORAGE.USER_DATA, JSON.stringify(userData));
                
                Utils.showSuccess("Login successful!");
                
                // Redirect based on role without reload
                const user = Utils.getCurrentUser();
                if (user.role === Constants.ROLES.ADMIN || user.role === Constants.ROLES.EMPLOYEE) {
                    window.location.hash = 'dashboard';
                } else {
                    window.location.hash = 'profile';
                }
                UserService.init(); // Reinitialize after login
            },
            function(error) {
                console.error('Login error:', error);
                const errorMessage = error.responseJSON?.message || "Login failed";
                $('#login-error').text(errorMessage).show();
                Utils.showError(errorMessage);
            }
        );
    },

    register: function(entity) {
        // Hide any previous error messages
        $('#register-error').hide();
        
        // Remove confirm_password from entity before sending
        delete entity.confirm_password;
        
        RestClient.auth.register(entity,
            function(response) {
                Utils.showSuccess("Registration successful! Please login.");
                
                // Switch to login form and pre-fill email
                $('#auth-signup').hide();
                $('#auth-login').show();
                $('#auth-email').val(entity.email);
            },
            function(error) {
                // Show error in the form
                const errorMessage = error.responseJSON?.message || "Registration failed";
                $('#register-error').text(errorMessage).show();
                Utils.showError(errorMessage);
            }
        );
    },

    logout: function() {
        // Clear all user data and redirect
        Utils.logout();
    },

    updateProfile: function(data) {
        RestClient.put(Constants.API.USER_PROFILE, data,
            function(response) {
                Utils.showSuccess("Profile updated successfully!");
                // Get current user data
                const currentUser = Utils.getCurrentUser();
                // Update only the changed fields
                const updatedUser = { ...currentUser, ...data };
                // Store updated user data
                localStorage.setItem(Constants.STORAGE.USER_DATA, JSON.stringify(updatedUser));
                // Refresh the page to show updated data
                window.location.reload();
            },
            function(error) {
                Utils.showError(error.responseJSON?.message || "Failed to update profile");
            }
        );
    },

    changePassword: function(currentPassword, newPassword) {
        RestClient.put(Constants.API.USER_PROFILE, {
            current_password: currentPassword,
            new_password: newPassword
        },
        function(response) {
            Utils.showSuccess("Password changed successfully!");
            // Optionally logout user after password change
            UserService.logout();
        },
        function(error) {
            Utils.showError(error.responseJSON?.message || "Failed to change password");
        });
    },

    // Method to initialize user-specific UI elements
    initUserUI: function() {
        const user = Utils.getCurrentUser();
        if (!user) return;

        // Update UI elements with user info
        $("#user-email").text(user.email);
        $("#user-role").text(user.role);

        // Show/hide elements based on user role
        if (Utils.hasRole(Constants.ROLES.ADMIN)) {
            $(".admin-only").show();
        }
        if (Utils.hasAnyRole([Constants.ROLES.ADMIN, Constants.ROLES.EMPLOYEE])) {
            $(".staff-only").show();
        }
    },

    // Method to fetch all users for admin dashboard
    getAllUsers: function(callback, error_callback) {
        RestClient.get(Constants.API.USERS, null, callback, error_callback);
    },

    // Method to populate users table in dashboard
    loadUsersForDashboard: function() {
        const user = Utils.getCurrentUser();
        if (!user || user.role !== Constants.ROLES.ADMIN) {
            console.log('Access denied: Only admins can view users');
            return;
        }

        UserService.getAllUsers(
            function(response) {
                console.log('Users loaded successfully:', response);
                UserService.populateUsersTable(response.data);
            },
            function(error) {
                console.error('Error loading users:', error);
                Utils.showError('Failed to load users');
            }
        );
    },

    // Method to populate the users list with data (matching products list style)
    populateUsersTable: function(users) {
        const usersList = $('#users-list');
        usersList.empty();

        if (!users || users.length === 0) {
            usersList.html('<div class="text-center py-3">No users found</div>');
            return;
        }

        users.forEach(function(user) {
            const fullName = `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'No name provided';
            const createdDate = new Date(user.created_at).toLocaleDateString();
            const userItem = `
                <div class="list-group-item" data-user-id="${user.id}">
                    <div class="row align-items-center">
                        <div class="col-2">
                            <div class="d-flex align-items-center justify-content-center" style="height: 100px; width: 100px; background-color: #f8f9fa; border-radius: 50%; margin: 0 auto;">
                                <i class="bi bi-person-fill" style="font-size: 2.5rem; color: #6c757d;"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="mb-1">${fullName}</h5>
                            <p class="mb-1"><strong>Email:</strong> ${user.email}</p>
                            <p class="mb-1"><strong>Role:</strong> <span class="badge bg-${UserService.getRoleBadgeColor(user.role)}">${user.role}</span></p>
                            <p class="mb-1"><strong>Member since:</strong> ${createdDate}</p>
                        </div>
                        <div class="col-4 text-end">
                            <small class="text-muted">User ID: ${user.id}</small>
                        </div>
                    </div>
                </div>`;
            usersList.append(userItem);
        });
    },

    // Helper method to get badge color for user roles
    getRoleBadgeColor: function(role) {
        switch(role) {
            case Constants.ROLES.ADMIN: return 'danger';
            case Constants.ROLES.EMPLOYEE: return 'warning';
            case Constants.ROLES.CUSTOMER: return 'secondary';
            default: return 'secondary';
        }
    }
};

// Make UserService available globally
window.UserService = UserService;

// Initialize the service when document is ready
$(document).ready(function() {
    // Ensure dependencies are loaded before initializing
    if (typeof Utils !== 'undefined' && typeof Constants !== 'undefined' && typeof RestClient !== 'undefined') {
        UserService.init();
    } else {
        console.error('Required dependencies not loaded');
    }
});

// Initialize on hash change
$(window).on('hashchange', function() {
    UserService.init();
});
