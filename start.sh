#!/bin/bash
# Resume AI - Quick Launcher for Linux/Mac

echo "========================================"
echo ""
echo "  >>> RESUME AI - AUTOMATIC LAUNCHER"
echo ""
echo "========================================"
echo ""

# Get the directory where this script is located
cd "$(dirname "$0")"

echo "Starting PHP Development Server..."
echo ""

# Check if php is available
if ! command -v php &> /dev/null; then
    echo "❌ Error: PHP is not installed or not in PATH"
    exit 1
fi

# Start PHP server
php -S localhost:8000 &
PHP_PID=$!

echo "✓ Server starting on http://localhost:8000"
echo "Waiting for server to be ready..."
sleep 3

# Open in default browser
if command -v xdg-open &> /dev/null; then
    xdg-open http://localhost:8000/setup.php
elif command -v open &> /dev/null; then
    open http://localhost:8000/setup.php
else
    echo "Please open your browser and go to: http://localhost:8000/setup.php"
fi

echo ""
echo "========================================"
echo "Setup page opening in your browser..."
echo "If it doesn't appear, visit:"
echo "http://localhost:8000/setup.php"
echo "========================================"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

# Wait for Ctrl+C
wait $PHP_PID
