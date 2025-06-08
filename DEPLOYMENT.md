# Deployment Instructions for Digital Ocean

This application has been prepared for deployment to Digital Ocean while maintaining compatibility with local development.

## Environment Configuration

The application now automatically detects whether it's running locally or in production based on the server hostname.

### For Production Deployment:

1. **Set Environment Variables** on your Digital Ocean server:
   ```bash
   export DB_HOST="localhost"
   export DB_NAME="watchland"
   export DB_USER="your_db_user"
   export DB_PASSWORD="your_secure_password"
   export JWT_SECRET="your_secure_jwt_secret"
   export FRONTEND_URL="https://your-domain.com"
   export BACKEND_URL="https://your-domain.com/backend"
   export ALLOWED_ORIGINS="https://your-domain.com,https://www.your-domain.com"
   ```

2. **Or create a .env file** in the root directory with the above variables.

3. **Database Setup**: Ensure your MySQL database is properly configured on Digital Ocean.

## Key Changes Made for Deployment

### 1. Environment Detection
- Automatic detection of local vs production environment
- Environment-specific configuration loading

### 2. Dynamic URL Configuration
- Frontend now fetches configuration from `/config` endpoint
- No more hardcoded localhost URLs
- Backward compatible with local development

### 3. Database Configuration
- Environment variable support for all database settings
- Secure password handling for production

### 4. CORS Configuration
- Environment-aware CORS headers
- Wildcard (`*`) for local development
- Specific origins for production security

### 5. Route Authentication
- Improved path matching for production URLs
- Public endpoints correctly excluded from authentication

## Testing

### Local Development
The application continues to work exactly as before locally:
- Uses default localhost URLs
- Uses existing database credentials
- Allows all CORS origins

### Production Testing
To test production configuration locally:
1. Set environment variables to production values
2. Update your hosts file to simulate production domain
3. Test all endpoints and functionality

## Files Modified

- `backend/config.php` - Added environment detection and configuration
- `backend/routes/ConfigRoutes.php` - New configuration endpoint
- `backend/middleware/AuthMiddleware.php` - Improved route exclusion
- `frontend/utils/constants.js` - Dynamic URL configuration
- `frontend/js/auth.js` - Uses dynamic URLs
- `frontend/services/product-service.js` - Uses dynamic URLs
- `index.php` - Environment-aware CORS
- `backend/index.php` - Environment-aware CORS

## Deployment Steps

1. Upload all files to your Digital Ocean server
2. Set environment variables or create .env file
3. Configure your web server (Apache/Nginx) to point to the root directory
4. Ensure your database is set up and accessible
5. Test the `/config` endpoint to verify configuration loading
6. Test authentication and product endpoints

## Security Notes

- JWT secret should be a strong, unique key in production
- Database passwords should be secure
- CORS origins should be limited to your actual domains
- Consider using HTTPS for all production traffic 