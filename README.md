# TimThumb - Enhanced Security Edition

TimThumb is a simple, flexible, PHP script that resizes images. This version includes comprehensive security enhancements to mitigate known vulnerabilities and provides a more robust implementation.

## Security Enhancements

This enhanced version includes the following security improvements:

1. **Improved MIME Type Validation**
   - Multiple validation methods including file signatures (magic bytes), file extension checking, and comprehensive MIME type verification
   - Cross-validation between detected MIME type and file extension
   - Prevention of MIME type spoofing attacks

2. **Enhanced Input Validation**
   - Strict URL parameter validation and sanitization
   - Prevention of directory traversal attacks
   - Null byte injection protection
   - Detection and blocking of malicious character sequences
   - Local IP address access restrictions

3. **Improved Cache Handling**
   - Secure permissions for cache directories
   - Protection against direct cache access
   - Improved cache cleaning and management

4. **Robust Error Handling**
   - Sanitized error outputs to prevent information disclosure
   - Context-aware error logging for security auditing
   - Rate limiting on error collection to prevent DoS

5. **Additional Security Headers**
   - Implementation of security headers to prevent common web vulnerabilities
   - Content security policy implementation

6. **Comprehensive Testing**
   - Included test script (`test-timthumb.php`) for validating security measures
   - Tests for common attack vectors and vulnerabilities


## Security Best Practices

To ensure the most secure implementation of TimThumb, follow these best practices:

1. **Limit External Image Sources**
   - Set `ALLOW_EXTERNAL` to `false` unless absolutely necessary
   - If external sources are required, use `ALLOWED_SITES` to specify a whitelist
   - Never set `ALLOW_ALL_EXTERNAL_SITES` to `true` in production environments

2. **Secure Your Cache Directory**
   - Place the cache directory outside of web-accessible folders when possible
   - Ensure proper permissions (typically 755 for directories, 644 for files)
   - Regularly clean the cache to prevent unauthorized access to cached files

3. **Set Reasonable Limits**
   - Use `MAX_FILE_SIZE` to limit the size of processed images
   - Set `MEMORY_LIMIT` appropriately for your server capabilities
   - Configure `MAX_WIDTH` and `MAX_HEIGHT` to prevent resource exhaustion

4. **Configure Error Handling**
   - In production, set `DISPLAY_ERROR_MESSAGES` to `false` to prevent information disclosure
   - Enable logging for security monitoring purposes

5. **Regular Updates**
   - Keep TimThumb updated with the latest security patches
   - Regularly run the included `test-timthumb.php` script to verify security measures

## Testing Security Measures

This version includes a comprehensive test script to validate the security measures:

```bash
php test-timthumb.php
```

The test script will check for common vulnerabilities and verify that the security enhancements are working correctly.

## Installing with composer

To use this repo on your composer project, simply add an *vcs* repository pointing to this GitHub repo and require it in your composer.json file:

```json
{
	"repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/GabrielGil/TimThumb"
		}
	],
	"require": {
		"gabrielgil/timthumb": "2.*"
	}
}
```

### A better way to use TimThumb

I think it's a good way of using timthumb, to store it on a non-public folder (Like the whole composer *vendor* folder) and then create your **own** resizer endpoint. If you use it with composer (as this repo is intended to), hide your vendor folder (Just an advice).

How you create your own app structure depends on you, or on your team. If your desired resize endpoint points to a specific file, you can use this code there.

```php
	
/* Redefine your with own defaults here.
 * This are just examples, no one is required. */

// Set the time the cache is cleaned (Since the image generation) to one month (2592000/60/60/24=30)
define ('FILE_CACHE_MAX_FILE_AGE', 2592000);
// Use the default system cache dir so your project's folder stays clean.
define ('FILE_CACHE_DIRECTORY', sys_get_temp_dir());
// Quality set to 100%
define ('DEFAULT_Q', 100);

// Start timthumb.
timthumb::start();

```

After this is set up, you can use all the parameters shown in the [official documentation](http://binarymoon.co.uk/projects/timthumb).

## Documentation

You can also check the original documentation at [binarymoon.uk](http://binarymoon.co.uk/projects/timthumb)

## Configuration Options

This enhanced security version supports all original TimThumb configuration options with some added security-focused options:

```php
// Basic Configuration
define('DEBUG_ON', false);                    // Enable debugging (set to false in production)
define('DISPLAY_ERROR_MESSAGES', false);      // Whether to display error messages to users
define('FILE_CACHE_ENABLED', true);           // Whether to enable file caching
define('FILE_CACHE_DIRECTORY', './cache');    // Directory to store cached files
define('FILE_CACHE_MAX_FILE_AGE', 86400);    // Maximum age of cached files in seconds (24 hours)

// Security Configuration
define('ALLOW_EXTERNAL', false);              // Whether to allow external websites
define('ALLOW_ALL_EXTERNAL_SITES', false);    // Whether to allow all external websites (security risk!)
define('ALLOWED_SITES', 'example.com,*.example.org'); // Whitelist of allowed external domains
define('BLOCK_EXTERNAL_LEECHERS', false);     // Whether to block external websites from accessing your images

// Resource Limits
define('MAX_FILE_SIZE', 5242880);            // Maximum file size (5MB)
define('MEMORY_LIMIT', '128M');               // PHP memory limit
define('MAX_WIDTH', 1500);                    // Maximum width of output image
define('MAX_HEIGHT', 1500);                   // Maximum height of output image
```

## Conclusion

This enhanced security version of TimThumb addresses many of the historical vulnerabilities while maintaining the core functionality that made TimThumb popular. By following the security best practices outlined in this document, you can use TimThumb safely in your projects.

The security enhancements in this version were contributed by Frank (2025) and build upon the work of Ben Gillbanks and Mark Maunder, the original creators of TimThumb.