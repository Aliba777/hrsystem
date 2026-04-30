# HR Connect Terraform Infrastructure

This Terraform configuration provisions a VPS instance for the HR Connect platform on DigitalOcean.

## 📋 Prerequisites

- **Terraform** >= 1.0 ([Install Guide](https://learn.hashicorp.com/tutorials/terraform/install-cli))
- **DigitalOcean Account** ([Sign up](https://www.digitalocean.com/))
- **DigitalOcean API Token** ([Create token](https://cloud.digitalocean.com/account/api/tokens))
- **SSH Key** uploaded to DigitalOcean ([Upload key](https://cloud.digitalocean.com/account/security))

## 🚀 Quick Start

### 1. Copy the example variables file

```bash
cd terraform
cp terraform.tfvars.example terraform.tfvars
```

### 2. Edit `terraform.tfvars` with your values

```hcl
do_token         = "your_digitalocean_api_token_here"
environment_name = "production"
region           = "fra1"
instance_type    = "s-2vcpu-4gb"
ssh_key_name     = "your_ssh_key_name"
```

### 3. Initialize Terraform

```bash
terraform init
```

This downloads the DigitalOcean provider and initializes the backend.

### 4. Preview changes

```bash
terraform plan
```

Review the planned changes before applying.

### 5. Apply configuration

```bash
terraform apply
```

Type `yes` when prompted to confirm.

### 6. Get outputs

```bash
terraform output
```

This displays:
- Server public IP address
- SSH connection string
- Web application URL
- phpMyAdmin URL

### 7. Connect to server

```bash
ssh root@<instance_public_ip>
```

### 8. Destroy infrastructure (when needed)

```bash
terraform destroy
```

Type `yes` when prompted to confirm.

## 📊 Variables

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `do_token` | DigitalOcean API token | - | Yes |
| `environment_name` | Environment (dev/staging/production) | `production` | No |
| `region` | DigitalOcean region | `fra1` | No |
| `instance_type` | Droplet size | `s-2vcpu-4gb` | No |
| `ssh_key_name` | SSH key name in DigitalOcean | - | Yes |

### Available Regions

- `nyc1` - New York 1
- `nyc3` - New York 3
- `sfo3` - San Francisco 3
- `fra1` - Frankfurt 1 (default)
- `lon1` - London 1
- `sgp1` - Singapore 1
- `ams3` - Amsterdam 3
- `tor1` - Toronto 1

### Available Instance Types

| Type | vCPU | RAM | SSD | Price/month |
|------|------|-----|-----|-------------|
| `s-1vcpu-1gb` | 1 | 1GB | 25GB | $6 |
| `s-1vcpu-2gb` | 1 | 2GB | 50GB | $12 |
| `s-2vcpu-2gb` | 2 | 2GB | 60GB | $18 |
| `s-2vcpu-4gb` | 2 | 4GB | 80GB | $24 ⭐ |
| `s-4vcpu-8gb` | 4 | 8GB | 160GB | $48 |

⭐ Recommended for production

## 🔥 Firewall Rules

The following ports are automatically configured:

| Port | Protocol | Service | Access |
|------|----------|---------|--------|
| 22 | TCP | SSH | Public |
| 80 | TCP | HTTP | Public |
| 443 | TCP | HTTPS | Public |
| 8080 | TCP | Web App (Docker) | Public |
| 8081 | TCP | phpMyAdmin (Docker) | Public |

## 📤 Outputs

After `terraform apply`, you'll get:

```
instance_id              = "droplet-12345678"
instance_public_ip       = "123.456.789.012"
instance_name            = "production-hr-connect"
instance_region          = "fra1"
instance_size            = "s-2vcpu-4gb"
firewall_id              = "firewall-87654321"
ssh_connection_string    = "ssh root@123.456.789.012"
web_url                  = "http://123.456.789.012:8080"
phpmyadmin_url           = "http://123.456.789.012:8081"
```

## 🔄 Adapting for Other Cloud Providers

### AWS (Amazon Web Services)

Replace `main.tf` provider block:

```hcl
provider "aws" {
  region = var.region
}

resource "aws_instance" "hr_connect_server" {
  ami           = "ami-0c55b159cbfafe1f0" # Ubuntu 22.04
  instance_type = "t3.medium"
  key_name      = var.ssh_key_name
  
  tags = {
    Name        = "${var.environment_name}-hr-connect"
    Environment = var.environment_name
  }
  
  vpc_security_group_ids = [aws_security_group.hr_connect_sg.id]
}

resource "aws_security_group" "hr_connect_sg" {
  name = "${var.environment_name}-hr-connect-sg"
  
  ingress {
    from_port   = 22
    to_port     = 22
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }
  
  # Add other ports...
}
```

### Hetzner Cloud

Replace `main.tf` provider block:

```hcl
provider "hcloud" {
  token = var.hcloud_token
}

resource "hcloud_server" "hr_connect_server" {
  name        = "${var.environment_name}-hr-connect"
  server_type = "cx21" # 2 vCPU, 4GB RAM
  image       = "ubuntu-22.04"
  location    = "fsn1" # Falkenstein, Germany
  ssh_keys    = [data.hcloud_ssh_key.main.id]
}
```

## 🛠️ Troubleshooting

### Error: "SSH key not found"

**Solution:** Upload your SSH key to DigitalOcean first:
```bash
# Generate SSH key if you don't have one
ssh-keygen -t ed25519 -C "your_email@example.com"

# Copy public key
cat ~/.ssh/id_ed25519.pub

# Upload at: https://cloud.digitalocean.com/account/security
```

### Error: "Invalid API token"

**Solution:** Check your token:
1. Go to https://cloud.digitalocean.com/account/api/tokens
2. Create a new token with read/write access
3. Update `terraform.tfvars` with the new token

### Error: "Region not available"

**Solution:** Choose a different region from the available list above.

### Error: "Insufficient quota"

**Solution:** Contact DigitalOcean support to increase your droplet limit.

## 🔒 Security Best Practices

1. **Never commit `terraform.tfvars`** - It contains sensitive data
2. **Use remote state storage** for production (S3, Terraform Cloud)
3. **Enable 2FA** on your DigitalOcean account
4. **Rotate API tokens** regularly
5. **Use separate workspaces** for dev/staging/production

## 📚 Additional Resources

- [Terraform Documentation](https://www.terraform.io/docs)
- [DigitalOcean Provider Docs](https://registry.terraform.io/providers/digitalocean/digitalocean/latest/docs)
- [DigitalOcean API Docs](https://docs.digitalocean.com/reference/api/)
- [Terraform Best Practices](https://www.terraform-best-practices.com/)

## 📝 License

This Terraform configuration is part of the HR Connect project.
