# Instructivo para Validación de RNF Pendientes

## RNF-04: Encuesta SUS (System Usability Scale)

Aplicar a 5 usuarios reales del taller después de usar el sistema 30 min.

### Cuestionario SUS (10 preguntas)

Marque 1 (Totalmente en desacuerdo) a 5 (Totalmente de acuerdo):

1. Creo que me gustaría usar este sistema con frecuencia.
2. Encontré el sistema innecesariamente complejo.
3. Pensé que el sistema era fácil de usar.
4. Creo que necesitaría apoyo técnico para usar este sistema.
5. Encontré que las funciones estaban bien integradas.
6. Pensé que había demasiada inconsistencia en el sistema.
7. Imagino que la mayoría aprendería a usar este sistema rápidamente.
8. Encontré el sistema muy engorroso de usar.
9. Me sentí muy seguro usando el sistema.
10. Necesitaba aprender muchas cosas antes de comenzar.

### Cálculo del puntaje SUS
- Items impares (1,3,5,7,9): restar 1 a la respuesta
- Items pares (2,4,6,8,10): 5 menos la respuesta
- Sumar todos los valores y multiplicar por 2.5
- Resultado ≥ 80 = CUMPLE

---

## RNF-06/07: Prueba de Carga con JMeter

1. Descargar Apache JMeter: https://jmeter.apache.org/
2. Abrir `prueba_carga.jmx` (incluido en este proyecto)
3. Configurar servidor objetivo (CTRL+R)
4. Ejecutar prueba con 10 hilos (usuarios concurrentes)
5. Verificar en el reporte: percentil 95 ≤ 2s

### Escenarios de prueba
- Consultar listado de clientes
- Consultar listado de vehículos
- Consultar órdenes de trabajo
- Buscar producto en POS
- Generar reporte de ventas

### Para prueba con 50,000 registros (RNF-07)
1. Ejecutar `php scripts/poblar_bd.php` para generar datos masivos
2. Ejecutar la misma prueba JMeter
3. Verificar tiempo de respuesta ≤ 3s

---

## RNF-08: Monitoreo con Uptime Robot

1. Crear cuenta gratuita en https://uptimerobot.com
2. Agregar monitor tipo HTTP(s)
3. URL: `https://sistema-taller-production-c558.up.railway.app`
4. Intervalo: 5 minutos
5. Alertas: Email
6. Monitorear por 30 días consecutivos
7. Verificar disponibilidad ≥ 99% en horario laboral (lun-sáb 8:00-18:00)

---

## RNF-09: Programar Backup Automático en Railway

### Opción 1: Railway Cron Job
1. Ir a Railway Dashboard > Proyecto > Cron Jobs
2. Crear nuevo Cron Job
3. Comando: `php backup.php`
4. Schedule: `0 2 * * *` (diario a las 2 AM)
5. Timezone: America/Lima

### Opción 2: Windows Task Scheduler
```
1. Abrir "Task Scheduler"
2. Crear tarea básica
3. Disparador: Diario a las 2:00 AM
4. Acción: Iniciar programa
5. Programa: php.exe
6. Argumentos: D:\ruta\sistema-taller\backup.php
```

### Verificar backups
```bash
ls backups/
# Debe mostrar archivos backup_YYYY-MM-DD_HH-MM-SS.sql
```

### Restaurar desde backup
```bash
php backup.php --restore backups/backup_2025-01-01_02-00-00.sql
```

---

## RNF-12: PhpSpreadsheet (.xlsx)

Ya implementado. ReporteController::exportarExcel() ahora genera .xlsx real con 3 hojas (Ventas, Órdenes, Stock Bajo).

### Instalar dependencia en Railway
```bash
composer install
```
Esto instalará `phpoffice/phpspreadsheet` automáticamente.

---

## RNF-12: API WhatsApp Business

Requiere:
1. Cuenta de WhatsApp Business API (Meta)
2. Número de teléfono verificado
3. Token de acceso

### Pasos para integrar
1. Crear app en https://developers.facebook.com
2. Configurar WhatsApp Business API
3. Obtener token permanente
4. Agregar variables de entorno en Railway:
   - `WHATSAPP_TOKEN=...`
   - `WHATSAPP_PHONE_ID=...`
5. Enviar notificaciones via API:
```php
$url = "https://graph.facebook.com/v21.0/" . getenv('WHATSAPP_PHONE_ID') . "/messages";
$headers = ["Authorization: Bearer " . getenv('WHATSAPP_TOKEN'), "Content-Type: application/json"];
// Enviar plantilla de estado de orden
```

Actualmente el sistema usa link directo `wa.me` como fallback.
