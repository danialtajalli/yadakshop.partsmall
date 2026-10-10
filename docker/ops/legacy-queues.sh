#!/bin/sh
# Queue metadata only; never prints payloads or changes Redis data.
set -u
redis-cli INFO keyspace
redis-cli CONFIG GET maxmemory maxmemory-policy appendonly appendfsync save
for database in $(seq 0 15); do
    redis-cli -n "$database" --scan --pattern '*queues:*' | while IFS= read -r key; do
        type=$(redis-cli -n "$database" TYPE "$key")
        case "$type" in
            list) count=$(redis-cli -n "$database" LLEN "$key") ;;
            zset) count=$(redis-cli -n "$database" ZCARD "$key") ;;
            *) continue ;;
        esac
        printf 'db=%s key=%s type=%s jobs=%s\n' "$database" "$key" "$type" "$count"
    done
done
for pid in $(pgrep -f 'artisan (queue:|schedule:)' || true); do
    printf 'artisan_pid=%s cwd=' "$pid"
    readlink "/proc/$pid/cwd" || true
done
systemctl list-timers --all --no-pager
