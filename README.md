# TokenMinds Docker Template

This is a templated Docker deployment for TokenMinds that allows easy replication by just changing the project name.

## Quick Setup

1. **Set up your project environment:**
   ```bash
   ./setup.sh [PROJECT_NAME]
   ```
   Example: `./setup.sh hcola` or `./setup.sh e3mel`

2. **Build and run:**
   ```bash
   docker build . --no-cache
   docker compose up -d
   ```

That's it! Your application will be available at: http://localhost:8080

## Manual Setup (Alternative)

If you prefer to set up manually:

1. **Copy environment template:**
   ```bash
   cp .env.template .env
   ```

2. **Edit .env file and change PROJECT_NAME:**
   ```bash
   # Change this line in .env:
   PROJECT_NAME=your_project_name
   ```

3. **Generate nginx configuration:**
   ```bash
   ./generate-nginx-config.sh
   ```

4. **Build and run:**
   ```bash
   docker build . --no-cache
   docker compose up -d
   ```

## Database Configuration

The application automatically configures the database connection. During installation:

1. **Database Host**: `database`
2. **Database Name**: `tokenminds`
3. **Database User**: `tokenminds_user` 
4. **Database Password**: `tokenminds_password`