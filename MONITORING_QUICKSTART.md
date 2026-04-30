# Мониторинг - Быстрый Старт

## 🚀 Запуск за 3 минуты

### 1. Запустить все сервисы

```bash
docker-compose up -d
```

### 2. Проверить статус

```bash
docker-compose ps
```

Должны работать:
- ✅ prometheus (порт 9090)
- ✅ node-exporter (порт 9100)
- ✅ grafana (порт 3000)
- ✅ alertmanager (порт 9093)

### 3. Открыть интерфейсы

| Сервис | URL | Логин/Пароль |
|--------|-----|--------------|
| **Grafana** | http://localhost:3000 | admin / admin |
| **Prometheus** | http://localhost:9090 | - |
| **Alertmanager** | http://localhost:9093 | - |

## 📊 Grafana - Первые шаги

1. Открыть http://localhost:3000
2. Войти: `admin` / `admin`
3. Импортировать готовый дашборд:
   - Dashboards → Import
   - ID: **1860** (Node Exporter Full)
   - Выбрать Prometheus datasource
   - Import

## 🚨 Проверка алертов

### Посмотреть активные алерты

```bash
# В Prometheus
http://localhost:9090/alerts

# В Alertmanager
http://localhost:9093/#/alerts
```

### Тестовый алерт

```bash
# Создать нагрузку на CPU
docker exec -it my_php bash -c "yes > /dev/null &"

# Подождать 5 минут - придёт уведомление в Telegram

# Остановить нагрузку
docker exec -it my_php pkill yes
```

## 📈 Полезные метрики

### В Prometheus (http://localhost:9090/graph)

Попробуйте эти запросы:

```promql
# CPU usage
100 - (avg(rate(node_cpu_seconds_total{mode="idle"}[5m])) * 100)

# Memory usage
(1 - (node_memory_MemAvailable_bytes / node_memory_MemTotal_bytes)) * 100

# Disk usage
(1 - (node_filesystem_avail_bytes / node_filesystem_size_bytes)) * 100
```

## 🔧 Остановка

```bash
# Остановить все
docker-compose down

# Остановить с удалением данных
docker-compose down -v
```

## 📚 Подробная документация

См. [MONITORING_README.md](MONITORING_README.md)

---

**Время настройки**: 3-5 минут  
**Использование RAM**: ~1GB
