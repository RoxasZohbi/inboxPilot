# Queue Troubleshooting Guide

This guide helps you reset and troubleshoot the Laravel queue system when jobs get stuck or fail.

## 🛑 Kill All Jobs and Reset Everything

Run these commands in order to completely reset the queue system:

### 1. Stop All Queue Workers
```bash
# Kill all queue worker processes
pkill -9 -f "queue:work"

# Verify no workers are running
ps aux | grep "queue:work" | grep -v grep
```

### 2. Clear All Queued Jobs
```bash
# Clear Redis queue
redis-cli FLUSHDB

# OR clear specific queue
redis-cli DEL queues:default

# Clear all queue-related Redis keys
redis-cli KEYS "queues:*" | xargs -r redis-cli DEL
```

### 3. Clear Failed Jobs
```bash
# Clear all failed jobs
php artisan queue:flush

# OR clear from database directly
php artisan tinker --execute="DB::table('failed_jobs')->delete(); echo 'Failed jobs cleared';"
```

### 4. Clear Pending Jobs from Database
```bash
php artisan tinker --execute="DB::table('jobs')->delete(); echo 'Pending jobs cleared';"
```

### 5. Clear Sync Status Cache
```bash
# Clear Gmail sync status cache
redis-cli DEL gmail_sync:1

# Clear all Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 6. Clear Application Data (Optional)
```bash
# If you want to start fresh with email syncing
php artisan tinker --execute="App\Models\Email::truncate(); echo 'Emails cleared';"

# Reset user's last sync time
php artisan tinker --execute="App\Models\User::first()->update(['last_synced_at' => null]); echo 'Sync time reset';"
```

## ✅ Start Fresh

After clearing everything, start the queue worker:

```bash
php artisan queue:work redis --timeout=900 --tries=3
```

Then trigger a new sync from the dashboard.

---

## 🔍 Monitoring & Debugging

### Check Queue Worker Status
```bash
# Check if queue worker is running
ps aux | grep "queue:work" | grep -v grep

# Check process details
ps aux | grep artisan
```

### Monitor Queue in Real-Time

**Option 1: Watch Queue Worker Output**
```bash
php artisan queue:work redis --timeout=900 --tries=3
```

**Option 2: Tail Laravel Logs**
```bash
tail -f storage/logs/laravel.log
```

**Option 3: Watch Email Count**
```bash
watch -n 2 'php artisan tinker --execute="echo \"Emails: \" . App\Models\Email::count() . PHP_EOL;"'
```

**Option 4: Monitor Sync Status**
```bash
# Check Redis cache for sync progress
redis-cli GET gmail_sync:1

# Pretty print JSON
redis-cli GET gmail_sync:1 | jq
```

### Check for Failed Jobs
```bash
# List failed jobs
php artisan queue:failed

# Retry specific failed job
php artisan queue:retry <job-id>

# Retry all failed jobs
php artisan queue:retry all
```

### Check Queue Metrics
```bash
# Count pending jobs in Redis
redis-cli LLEN queues:default

# Count failed jobs
php artisan tinker --execute="echo 'Failed jobs: ' . DB::table('failed_jobs')->count() . PHP_EOL;"

# Count emails in database
php artisan tinker --execute="echo 'Total emails: ' . App\Models\Email::count() . PHP_EOL;"
```

---

## 🐛 Common Issues & Solutions

### Issue: "Datatype mismatch: column is_unread is of type boolean"
**Solution:** The boolean casting is fixed in `ProcessEmailJob.php`. Make sure queue worker is restarted after code changes.

```bash
# Restart queue worker
pkill -9 -f "queue:work"
php artisan queue:work redis --timeout=900 --tries=3
```

### Issue: "401 UNAUTHENTICATED - Invalid authentication credentials"
**Solution:** OAuth token expired. The system auto-refreshes tokens now, but if it fails:

```bash
# Check logs for token refresh errors
tail -100 storage/logs/laravel.log | grep "Token refresh"

# Re-authenticate: Logout and login again via Google OAuth
```

### Issue: "Sanctum AuthenticateSession password hash error"
**Solution:** OAuth users don't have passwords. Set a dummy password:

```bash
php artisan tinker --execute="DB::table('users')->where('id', 1)->update(['password' => bcrypt('temp_password')]); echo 'Password set';"
```

### Issue: Jobs keep failing and retrying forever
**Solution:** Clear everything and check for code errors:

```bash
# Clear all jobs
pkill -9 -f "queue:work"
redis-cli FLUSHDB
php artisan queue:flush

# Check recent errors
tail -50 storage/logs/laravel.log

# Start fresh
php artisan queue:work redis --timeout=900 --tries=3
```

### Issue: Queue worker times out
**Solution:** Increase timeout (default 60s is too short for 500 emails):

```bash
# Use 900 seconds (15 minutes)
php artisan queue:work redis --timeout=900 --tries=3
```

### Issue: "Sync already in progress" but no jobs running
**Solution:** Clear the Redis cache lock:

```bash
redis-cli DEL gmail_sync:1
php artisan cache:clear
```

---

## 📊 Check Sync API Status

From terminal:
```bash
curl -s http://127.0.0.1:8000/api/gmail/sync-status | jq
```

From browser DevTools:
```javascript
fetch('/api/gmail/sync-status')
  .then(r => r.json())
  .then(console.log);
```

---

## 🚀 Production Recommendations

### Use Supervisor for Queue Workers

1. Install Supervisor:
```bash
sudo apt install supervisor
```

2. Create config: `/etc/supervisor/conf.d/laravel-worker.conf`
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/InboxPilot/artisan queue:work redis --timeout=900 --tries=3 --sleep=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/InboxPilot/storage/logs/worker.log
stopwaitsecs=3600
```

3. Start Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### Schedule Background Sync (Optional)

Add to `routes/console.php`:
```php
Schedule::command('sync:gmail-all-users')->everyThirtyMinutes();
```

---

## 🔧 Quick Reset Script

Save this as `reset-queue.sh`:

```bash
#!/bin/bash

echo "🛑 Stopping queue workers..."
pkill -9 -f "queue:work"

echo "🗑️ Clearing Redis..."
redis-cli FLUSHDB

echo "🗑️ Clearing failed jobs..."
php artisan queue:flush

echo "🗑️ Clearing pending jobs..."
php artisan tinker --execute="DB::table('jobs')->delete();"

echo "🗑️ Clearing cache..."
php artisan cache:clear
php artisan config:clear

echo "✅ Reset complete! Start queue worker with:"
echo "php artisan queue:work redis --timeout=900 --tries=3"
```

Make it executable:
```bash
chmod +x reset-queue.sh
./reset-queue.sh
```

---

## 📞 Support

If issues persist:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check Redis is running: `redis-cli PING` (should return `PONG`)
3. Check database connection: `php artisan tinker --execute="DB::connection()->getPdo();"`
4. Verify queue configuration: `config/queue.php` should have `redis` as default connection

---

**Last Updated:** January 11, 2026
