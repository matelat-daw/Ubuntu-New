#!/bin/bash
# Install Cart & Stock Management Cron Job

echo "Installing Cart & Stock Management Cron..."

# Define cron job
CRON_JOB="0 2 */2 * * /usr/bin/php /var/www/html/api/cron/cart_stock_manager.php >> /var/www/html/api/logs/cron.log 2>&1"

# Check if cron job already exists
if crontab -l 2>/dev/null | grep -q "cart_stock_manager.php"; then
    echo "Cron job already installed!"
    echo "Current cron jobs:"
    crontab -l | grep "cart_stock_manager.php"
else
    # Add cron job
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    echo "Cron job installed successfully!"
    echo "Schedule: Every 48 hours at 2:00 AM"
fi

# Ensure logs directory exists and is writable
mkdir -p /var/www/html/api/logs
chmod 755 /var/www/html/api/logs

# Make cron script executable
chmod +x /var/www/html/api/cron/cart_stock_manager.php

echo ""
echo "✅ Installation complete!"
echo ""
echo "To test the cron manually, run:"
echo "  php /var/www/html/api/cron/cart_stock_manager.php"
echo ""
echo "To view logs:"
echo "  tail -f /var/www/html/api/logs/cron.log"
echo ""
echo "To verify cron is installed:"
echo "  crontab -l | grep cart_stock_manager"
