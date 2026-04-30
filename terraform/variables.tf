# Terraform variables for HR Connect infrastructure

variable "do_token" {
  description = "DigitalOcean API token for authentication"
  type        = string
  sensitive   = true
}

variable "environment_name" {
  description = "Environment name (e.g., dev, staging, production)"
  type        = string
  default     = "production"
  
  validation {
    condition     = contains(["dev", "staging", "production"], var.environment_name)
    error_message = "Environment name must be one of: dev, staging, production."
  }
}

variable "region" {
  description = "DigitalOcean region for the droplet"
  type        = string
  default     = "fra1" # Frankfurt, Germany
  
  validation {
    condition     = contains(["nyc1", "nyc3", "sfo3", "fra1", "lon1", "sgp1", "ams3", "tor1"], var.region)
    error_message = "Region must be a valid DigitalOcean region."
  }
}

variable "instance_type" {
  description = "Droplet size/type (CPU and RAM configuration)"
  type        = string
  default     = "s-2vcpu-4gb" # 2 vCPU, 4GB RAM, 80GB SSD
  
  validation {
    condition     = can(regex("^s-", var.instance_type))
    error_message = "Instance type must be a valid DigitalOcean droplet size (e.g., s-2vcpu-4gb)."
  }
}

variable "ssh_key_name" {
  description = "Name of existing SSH key in DigitalOcean account"
  type        = string
}
