#!/bin/bash
# Quick script to check and install PHP intl extension

echo "Checking PHP intl extension..."

if php -m | grep -qi intl; then
    echo "✅ PHP intl extension is installed!"
    php -m | grep -i intl
else
    echo "❌ PHP intl extension is NOT installed"
    echo ""
    echo "To install, run:"
    echo "  sudo apt-get update"
    echo "  sudo apt-get install php8.2-intl"
    echo ""
    echo "Then restart your web server:"
    echo "  sudo systemctl restart apache2"
    echo "  # OR"
    echo "  sudo systemctl restart php8.2-fpm && sudo systemctl restart nginx"
fi

