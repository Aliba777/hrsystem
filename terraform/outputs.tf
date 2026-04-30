# Terraform outputs for HR Connect infrastructure

output "instance_id" {
  description = "ID of the created DigitalOcean droplet"
  value       = digitalocean_droplet.hr_connect_server.id
}

output "instance_public_ip" {
  description = "Public IPv4 address of the server"
  value       = digitalocean_droplet.hr_connect_server.ipv4_address
}

output "instance_name" {
  description = "Name of the server instance"
  value       = digitalocean_droplet.hr_connect_server.name
}

output "instance_region" {
  description = "Region where the server is deployed"
  value       = digitalocean_droplet.hr_connect_server.region
}

output "instance_size" {
  description = "Size/type of the droplet"
  value       = digitalocean_droplet.hr_connect_server.size
}

output "firewall_id" {
  description = "ID of the firewall protecting the server"
  value       = digitalocean_firewall.hr_connect_firewall.id
}

output "ssh_connection_string" {
  description = "SSH command to connect to the server"
  value       = "ssh root@${digitalocean_droplet.hr_connect_server.ipv4_address}"
}

output "web_url" {
  description = "URL to access the web application"
  value       = "http://${digitalocean_droplet.hr_connect_server.ipv4_address}:8080"
}

output "phpmyadmin_url" {
  description = "URL to access phpMyAdmin"
  value       = "http://${digitalocean_droplet.hr_connect_server.ipv4_address}:8081"
}
