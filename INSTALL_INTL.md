# Install PHP intl Extension

## Quick Install

Run this command to install the PHP intl extension:

```bash
sudo apt-get update
sudo apt-get install php8.2-intl
```

After installation, restart your web server:

```bash
# For Apache
sudo systemctl restart apache2

# For Nginx with PHP-FPM
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

## Verify Installation

Check if intl is installed:

```bash
php -m | grep intl
```

You should see `intl` in the output.

## Alternative: If you can't use sudo

If you don't have sudo access, you may need to:
1. Contact your system administrator
2. Or use a different PHP installation method (like compiling from source)

## After Installation

Once installed, clear Laravel caches:

```bash
cd /home/srinu/Projects/AptKey
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

Then try accessing your Filament panels again.

