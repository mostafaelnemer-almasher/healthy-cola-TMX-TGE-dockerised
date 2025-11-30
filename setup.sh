#!/bin/bash

# TokenLite Docker Template Setup Script
# Usage: ./setup.sh [PROJECT_NAME]

set -e

# Get project name from argument or prompt user
if [ -z "$1" ]; then
    echo "Enter project name (e.g., e3mel, hcola): "
    read PROJECT_NAME
else
    PROJECT_NAME="$1"
fi

# Validate project name
if [ -z "$PROJECT_NAME" ]; then
    echo "Error: Project name cannot be empty"
    exit 1
fi

echo "Setting up TokenLite Docker environment for project: $PROJECT_NAME"

# Copy .env.template to .env and set PROJECT_NAME
if [ -f ".env.template" ]; then
    cp .env.template .env
    sed -i "s/PROJECT_NAME=hcola/PROJECT_NAME=$PROJECT_NAME/g" .env
    echo "Created .env file with PROJECT_NAME=$PROJECT_NAME"
else
    echo "Warning: .env.template not found, creating basic .env file"
    cat > .env << EOF
PROJECT_NAME=$PROJECT_NAME
APP_PORT=80
DOCKER_TARGET=production
DB_DATABASE=tokenlite
DB_USERNAME=tokenlite_user
DB_PASSWORD=tokenlite_password
DB_ROOT_PASSWORD=root_password
EOF
fi

# Generate nginx configuration
echo "Generating nginx configuration..."
export PROJECT_NAME
./generate-nginx-config.sh

echo ""
echo "==================================="
echo "Setup completed for project: $PROJECT_NAME"
echo "==================================="
echo ""
echo "To build and run the application:"
echo "1. docker build . --no-cache"
echo "2. docker compose up -d"
echo ""
echo "Your application will be available at: http://localhost:8080"
echo "Container names will be prefixed with: tokenlite-*-$PROJECT_NAME"
echo ""