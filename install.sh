```bash
#!/bin/bash

set -e

REPO_URL="https://github.com/Mohammed-Fawaz-Ali/Weather_Laravel.git"
PROJECT_NAME="Weather_Laravel"

echo "========== Laravel Auto Installer =========="

############################################
# Install Git if not installed
############################################
if ! command -v git &> /dev/null
then
    echo "Git not found. Installing..."
    sudo dnf install -y git
else
    echo "Git is already installed."
fi

############################################
# Install Docker if not installed
############################################
if ! command -v docker &> /dev/null
then
    echo "Docker not found. Installing..."

    sudo dnf remove -y docker \
        docker-client \
        docker-client-latest \
        docker-common \
        docker-latest \
        docker-latest-logrotate \
        docker-logrotate \
        docker-engine || true

    sudo dnf install -y dnf-plugins-core

    sudo dnf config-manager --add-repo https://download.docker.com/linux/rhel/docker-ce.repo

    sudo dnf install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

    sudo systemctl enable docker
    sudo systemctl start docker

else
    echo "Docker is already installed."
fi

############################################
# Verify Docker Compose
############################################
if docker compose version &> /dev/null
then
    echo "Docker Compose is available."
else
    echo "Docker Compose plugin missing."
    sudo dnf install -y docker-compose-plugin
fi

############################################
# Clone or Update Project
############################################
if [ -d "$PROJECT_NAME" ]
then
    echo "Project exists."

    cd "$PROJECT_NAME"

    git pull

else
    echo "Downloading project..."

    git clone "$REPO_URL"

    cd "$PROJECT_NAME"
fi

############################################
# Create .env if missing
############################################
if [ ! -f ".env" ]
then
    cp .env.example .env
fi

############################################
# Start Application
############################################
docker compose up -d --build

echo ""
echo "====================================="
echo "Deployment completed successfully."
echo "====================================="
```
