#!/bin/bash
set -euo pipefail
test "$(id -u)" = 0
unit=${1:?Pass the validated local-copy mount unit path}
test -f "$unit"
test ! -e /srv/partsmall
test ! -L /srv/partsmall
test ! -e /etc/systemd/system/srv-partsmall-qr.mount
test ! -L /etc/systemd/system/srv-partsmall-qr.mount
test "$(docker volume inspect --format '{{.Mountpoint}}' partsmall-prod-uploads)" = /var/lib/docker/volumes/partsmall-prod-uploads/_data
test "$(realpath /var/lib/docker/volumes/partsmall-prod-uploads/_data/img)" = /var/lib/docker/volumes/partsmall-prod-uploads/_data/img
systemd-analyze verify "$unit"
install -d -m 0750 -o root -g ubuntu /srv/partsmall
install -d -m 0750 -o root -g ubuntu /srv/partsmall/qr
install -m 0644 -o root -g root "$unit" /etc/systemd/system/srv-partsmall-qr.mount
systemctl daemon-reload
systemctl enable --now srv-partsmall-qr.mount
test "$(stat -c '%d:%i' /srv/partsmall/qr)" = "$(stat -c '%d:%i' /var/lib/docker/volumes/partsmall-prod-uploads/_data/img)"
systemctl is-enabled srv-partsmall-qr.mount
systemctl is-active srv-partsmall-qr.mount
runuser -u ubuntu -- test -w /srv/partsmall/qr
