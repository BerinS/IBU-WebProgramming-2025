// Prevent redeclaration by checking if UserService already exists
var UserService = window.UserService || {
    init: function() {
        // Check if required dependencies are loaded
        if (typeof Utils === 'undefined' || typeof Constants === 'undefined' || typeof RestClient === 'undefined') {
            console.error('Required dependencies not loaded');
            return;
        }

        // Check if user is already logged in
        if (Utils.isAuthenticated()) {
            // Redirect based on user role
            const user = Utils.getCurrentUser();
            if (user.role === Constants.ROLES.ADMIN) {
                window.location.href = "/IBU-WebProgramming-2025/frontend/index.html#admin";
                window.location.reload();
            } else if (user.role === Constants.ROLES.EMPLOYEE) {
                window.location.href = "/IBU-WebProgramming-2025/frontend/index.html#employee";
                window.location.reload();
            } else {
                window.location.href = "/IBU-WebProgramming-2025/frontend/index.html";
                window.location.reload();
            }
            return;
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
        
        console.log('Login attempt with email:', entity.email);
        
        RestClient.auth.login(entity.email, entity.password, 
            function(response) {
                console.log('Login successful!');
                console.log('Login response:', response);
                console.log('Current user:', Utils.getCurrentUser());
                Utils.showSuccess("Login successful!");
            },
            function(error) {
                // Show detailed error information
                console.error('Login error:', error);
                console.error('Status code:', error.status);
                console.error('Response:', error.responseJSON);
                
                // Show error in the form
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
        RestClient.auth.logout();
    },

    updateProfile: function(data) {
        RestClient.put(Constants.API.USER_PROFILE, data,
            function(response) {
                Utils.showSuccess("Profile updated successfully!");
                // Update stored user data
                localStorage.setItem(Constants.STORAGE.USER_DATA, JSON.stringify(response.data));
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
