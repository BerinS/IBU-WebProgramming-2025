// Prevent redeclaration by checking if Utils already exists
var Utils = window.Utils || {
    datatable: function (table_id, columns, data, pageLength=15) {
        if ($.fn.dataTable.isDataTable("#" + table_id)) {
          $("#" + table_id)
            .DataTable()
            .destroy();
        }
        $("#" + table_id).DataTable({
          data: data,
          columns: columns,
          pageLength: pageLength,
          lengthMenu: [2, 5, 10, 15, 25, 50, 100, "All"],
        });
    },
    parseJwt: function(token) {
        if (!token) return null;
        try {
            const payload = token.split('.')[1];
            const decoded = atob(payload);
            return JSON.parse(decoded);
        } catch (e) {
            console.error("Invalid JWT token", e);
            return null;
        }
    },
    isAuthenticated: function() {
        const token = localStorage.getItem(Constants.STORAGE.USER_TOKEN);
        if (!token) return false;

        const decoded = Utils.parseJwt(token);
        if (!decoded) return false;

        const currentTime = Date.now() / 1000;
        return decoded.exp > currentTime;
    },
    getCurrentUser: function() {
        if (!Utils.isAuthenticated()) return null;
        
        const userData = localStorage.getItem(Constants.STORAGE.USER_DATA);
        return userData ? JSON.parse(userData) : null;
    },
    hasRole: function(role) {
        const user = Utils.getCurrentUser();
        return user && user.role === role;
    },
    hasAnyRole: function(roles) {
        const user = Utils.getCurrentUser();
        return user && roles.includes(user.role);
    },
    hasPermission: function(permission) {
        const user = Utils.getCurrentUser();
        return user && user.permissions && user.permissions.includes(permission);
    },
    logout: function() {
        localStorage.removeItem(Constants.STORAGE.USER_TOKEN);
        localStorage.removeItem(Constants.STORAGE.USER_DATA);
        //window.location.href = '/IBU-WebProgramming-2025/frontend/index.html#register_login';
    },
    showError: function(message) {
        toastr.error(message || 'An error occurred');
    },
    showSuccess: function(message) {
        toastr.success(message);
    }
};

// Make Utils available globally
window.Utils = Utils;
 