# HR Connect - Monitoring System

Comprehensive monitoring solution using Prometheus, Grafana, Node Exporter, and Alertmanager.

## 🎯 Overview

The monitoring system provides:
- **Prometheus**: Metrics collection and storage (30-day retention)
- **Node Exporter**: System metrics (CPU, RAM, disk, network)
- **Grafana**: Visualization dashboards
- **Alertmanager**: Alert routing to Telegram

## 🚀 Quick Start

### 1. Start Monitoring Services

```bash
docker-compose up -d prometheus node-exporter grafana alertmanager
```

### 2. Access Web Interfaces

| Service | URL | Credentials |
|---------|-----|-------------|
| **Prometheus** | http://localhost:9090 | No auth |
| **Grafana** | http://localhost:3000 | admin / admin |
| **Alertmanager** | http://localhost:9093 | No auth |
| **Node Exporter** | http://localhost:9100/metrics | No auth |

### 3. Verify Services

```bash
# Check all services are running
docker-compose ps

# Check Prometheus targets
curl http://localhost:9090/api/v1/targets

# Check Node Exporter metrics
curl http://localhost:9100/metrics
```

## 📊 Grafana Dashboards

### Access Grafana

1. Open http://localhost:3000
2. Login: `admin` / `admin`
3. Prometheus datasource is pre-configured

### Import System Dashboard

1. Go to Dashboards → Import
2. Use dashboard ID: **1860** (Node Exporter Full)
3. Select Prometheus datasource
4. Click Import

### Key Metrics to Monitor

- **CPU Usage**: `100 - (avg(rate(node_cpu_seconds_total{mode="idle"}[5m])) * 100)`
- **Memory Usage**: `(1 - (node_memory_MemAvailable_bytes / node_memory_MemTotal_bytes)) * 100`
- **Disk Usage**: `(1 - (node_filesystem_avail_bytes / node_filesystem_size_bytes)) * 100`
- **Network Traffic**: `rate(node_network_receive_bytes_total[5m])`

## 🚨 Alert Rules

### Configured Alerts

| Alert | Condition | Severity | Duration |
|-------|-----------|----------|----------|
| **HighCPUUsage** | CPU > 80% | WARNING | 5 minutes |
| **HighMemoryUsage** | Memory > 90% | CRITICAL | 5 minutes |
| **HighDiskUsage** | Disk > 85% | WARNING | 2 minutes |
| **ServiceDown** | Service down | CRITICAL | 1 minute |
| **HighLoadAverage** | Load > CPU count | WARNING | 5 minutes |

### Alert Flow

```
Prometheus → Evaluates alert rules every 15s
     ↓
Alertmanager → Groups alerts (5-minute window)
     ↓
Webhook → alertmanager_webhook.php
     ↓
Telegram Bot → Sends notification
```

### Test Alerts

```bash
# Simulate high CPU (run in container)
docker exec -it my_php bash -c "yes > /dev/null &"

# Stop the process after testing
docker exec -it my_php pkill yes

# Check Alertmanager
curl http://localhost:9093/api/v2/alerts
```

## 🔧 Configuration Files

### Prometheus Configuration

**File**: `prometheus/prometheus.yml`

```yaml
global:
  scrape_interval: 15s
  evaluation_interval: 15s

scrape_configs:
  - job_name: 'node-exporter'
    static_configs:
      - targets: ['node-exporter:9100']
```

### Alert Rules

**File**: `prometheus/alerts.yml`

Contains all alert rule definitions with PromQL expressions.

### Alertmanager Configuration

**File**: `alertmanager/alertmanager.yml`

Routes alerts to Telegram via webhook.

## 📈 Prometheus Queries

### Useful PromQL Queries

```promql
# CPU usage per core
100 - (avg by(cpu) (rate(node_cpu_seconds_total{mode="idle"}[5m])) * 100)

# Available memory in GB
node_memory_MemAvailable_bytes / 1024 / 1024 / 1024

# Disk usage percentage
(1 - (node_filesystem_avail_bytes / node_filesystem_size_bytes)) * 100

# Network receive rate in MB/s
rate(node_network_receive_bytes_total[5m]) / 1024 / 1024

# System uptime in days
(time() - node_boot_time_seconds) / 86400
```

## 🔍 Troubleshooting

### Prometheus Not Scraping Targets

```bash
# Check Prometheus logs
docker logs prometheus

# Check targets status
curl http://localhost:9090/api/v1/targets | jq

# Verify Node Exporter is accessible
curl http://node-exporter:9100/metrics
```

### Grafana Can't Connect to Prometheus

```bash
# Check Grafana logs
docker logs grafana

# Test Prometheus from Grafana container
docker exec -it grafana wget -O- http://prometheus:9090/api/v1/query?query=up

# Verify datasource configuration
cat grafana/provisioning/datasources/prometheus.yml
```

### Alerts Not Sending to Telegram

```bash
# Check Alertmanager logs
docker logs alertmanager

# Check webhook endpoint
curl -X POST http://localhost:8080/alertmanager_webhook.php \
  -H "Content-Type: application/json" \
  -d '{"alerts":[{"status":"firing","labels":{"alertname":"Test","severity":"info"},"annotations":{"summary":"Test alert"}}]}'

# Verify Telegram credentials
docker exec -it my_php env | grep TELEGRAM
```

### High Resource Usage

```bash
# Check container resource usage
docker stats

# Reduce Prometheus retention
# Edit prometheus/prometheus.yml and change --storage.tsdb.retention.time

# Reduce scrape frequency
# Edit prometheus/prometheus.yml and increase scrape_interval
```

## 📊 Metrics Retention

- **Prometheus**: 30 days (configurable in docker-compose.yml)
- **Grafana**: Persistent (stored in grafana_data volume)
- **Alertmanager**: Persistent (stored in alertmanager_data volume)

## 🔐 Security Considerations

1. **Grafana**: Change default admin password after first login
2. **Prometheus**: Consider adding authentication for production
3. **Alertmanager**: Webhook uses internal Docker network
4. **Firewall**: Only expose necessary ports (3000, 9090, 9093)

## 📦 Backup and Restore

### Backup Prometheus Data

```bash
docker run --rm -v prometheus_data:/data -v $(pwd):/backup alpine tar czf /backup/prometheus_backup.tar.gz /data
```

### Restore Prometheus Data

```bash
docker run --rm -v prometheus_data:/data -v $(pwd):/backup alpine tar xzf /backup/prometheus_backup.tar.gz -C /
```

### Backup Grafana Dashboards

```bash
docker run --rm -v grafana_data:/data -v $(pwd):/backup alpine tar czf /backup/grafana_backup.tar.gz /data
```

## 🎓 Learning Resources

- [Prometheus Documentation](https://prometheus.io/docs/)
- [Grafana Documentation](https://grafana.com/docs/)
- [PromQL Basics](https://prometheus.io/docs/prometheus/latest/querying/basics/)
- [Node Exporter Metrics](https://github.com/prometheus/node_exporter)

## 🆘 Support

For issues:
1. Check logs: `docker-compose logs <service>`
2. Verify configuration files
3. Test connectivity between services
4. Review Prometheus targets: http://localhost:9090/targets

---

**Estimated Setup Time**: 10-15 minutes
**Resource Usage**: ~1GB RAM total (all monitoring services)
