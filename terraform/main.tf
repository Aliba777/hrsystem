# Terraform configuration for HR Connect infrastructure
# Provider: DigitalOcean (can be adapted for AWS/Hetzner)

terraform {
  required_version = ">= 1.0"
  
  required_providers {
    digitalocean = {
      source  = "digitalocean/digitalocean"
      version = "~> 2.0"
    }
  }
  
  # Remote state storage (optional - uncomment for production)
  # backend "s3" {
  #   bucket = "hr-connect-terraform-state"
  #   key    = "infrastructure/terraform.tfstate"
  #   region = "us-east-1"
  # }
}

# Configure DigitalOcean provider
provider "digitalocean" {
  token = var.do_token
}

# Lookup existing SSH key
data "digitalocean_ssh_key" "main" {
  name = var.ssh_key_name
}

# Create VPS instance (Droplet)
resource "digitalocean_droplet" "hr_connect_server" {
  # Instance configuration
  name   = "${var.environment_name}-hr-connect"
  region = var.region
  size   = var.instance_type
  image  = "ubuntu-22-04-x64"
  
  # SSH key for access
  ssh_keys = [
    data.digitalocean_ssh_key.main.id
  ]
  
  # Tags for organization
  tags = [
    "environment:${var.environment_name}",
    "project:hr-connect",
    "managed-by:terraform"
  ]
  
  # User data script for initial setup
  user_data = <<-EOF
    #!/bin/bash
    set -e
    
    # Update system
    apt-get update
    apt-get upgrade -y
    
    # Install Docker
    apt-get install -y \
      apt-transport-https \
      ca-certificates \
      curl \
      gnupg \
      lsb-release
    
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
    
    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
      $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
    
    apt-get update
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
    
    # Install Docker Compose standalone
    curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
    
    # Install Git
    apt-get install -y git
    
    # Enable and start Docker
    systemctl enable docker
    systemctl start docker
    
    # Create application directory
    mkdir -p /opt/hr-connect
    chown -R root:root /opt/hr-connect
    
    # Create backup directory
    mkdir -p /var/backups/hr_connect
    chmod 700 /var/backups/hr_connect
    
    echo "Server setup completed" > /var/log/user-data.log
  EOF
}

# Create firewall rules
resource "digitalocean_firewall" "hr_connect_firewall" {
  name = "${var.environment_name}-hr-connect-firewall"
  
  droplet_ids = [digitalocean_droplet.hr_connect_server.id]
  
  # SSH access (port 22)
  inbound_rule {
    protocol         = "tcp"
    port_range       = "22"
    source_addresses = ["0.0.0.0/0", "::/0"]
  }
  
  # HTTP access (port 80)
  inbound_rule {
    protocol         = "tcp"
    port_range       = "80"
    source_addresses = ["0.0.0.0/0", "::/0"]
  }
  
  # HTTPS access (port 443)
  inbound_rule {
    protocol         = "tcp"
    port_range       = "443"
    source_addresses = ["0.0.0.0/0", "::/0"]
  }
  
  # Web application - Docker (port 8080)
  inbound_rule {
    protocol         = "tcp"
    port_range       = "8080"
    source_addresses = ["0.0.0.0/0", "::/0"]
  }
  
  # phpMyAdmin - Docker (port 8081)
  inbound_rule {
    protocol         = "tcp"
    port_range       = "8081"
    source_addresses = ["0.0.0.0/0", "::/0"]
  }
  
  # Allow all outbound traffic (TCP)
  outbound_rule {
    protocol              = "tcp"
    port_range            = "1-65535"
    destination_addresses = ["0.0.0.0/0", "::/0"]
  }
  
  # Allow all outbound traffic (UDP)
  outbound_rule {
    protocol              = "udp"
    port_range            = "1-65535"
    destination_addresses = ["0.0.0.0/0", "::/0"]
  }
  
  # Allow ICMP (ping)
  outbound_rule {
    protocol              = "icmp"
    destination_addresses = ["0.0.0.0/0", "::/0"]
  }
}
