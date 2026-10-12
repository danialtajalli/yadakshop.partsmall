#!/bin/bash
set -euo pipefail
mode=${1:-check}
[[ "$mode" = check || "$mode" = apply ]] || exit 2
test "$(id -u)" = 0
release=/var/www/partsmall-releases/production-20261008
test "$(docker inspect --format '{{index .Config.Labels "com.docker.compose.project.working_dir"}}' partsmall-prod-app-1)" = "$release"
test "$(docker volume inspect --format '{{.Mountpoint}}' partsmall-prod-uploads)" = /var/lib/docker/volumes/partsmall-prod-uploads/_data
export PARTSMALL_ACCESS_MODE=$mode
python3 - <<'PY'
import datetime, fnmatch, hashlib, json, os, pathlib, pwd, subprocess

release = pathlib.Path('/var/www/partsmall-releases/production-20261008')
images = pathlib.Path('/var/lib/docker/volumes/partsmall-prod-uploads/_data/img')
assert release.resolve() == release and images.resolve() == images
uid = str(pwd.getpwnam('ubuntu').pw_uid)
allowed_roots = {'app', 'bootstrap', 'config', 'database', 'docker', 'lang', 'public', 'resources', 'routes', 'tests'}
protected = {'.git', '.idea', '.vscode', 'vendor', 'node_modules', 'storage', 'uploads', 'cache', 'build', 'backups', 'changes'}
protected_patterns = ['.env*', 'auth.json', '*.sql', '*.sql.gz', '*.dump', '*.zip', '*.tar', '*.tar.gz', '*.log', '*.pem', '*.key', 'hot']
targets = [release]
for entry in sorted(release.iterdir()):
    if entry.is_symlink() or entry.name in protected or any(fnmatch.fnmatch(entry.name, pattern) for pattern in protected_patterns):
        continue
    if entry.is_file():
        targets.append(entry)
    elif entry.is_dir() and entry.name in allowed_roots:
        for root, directories, files in os.walk(entry, followlinks=False):
            directories[:] = sorted(name for name in directories if name not in protected and not pathlib.Path(root, name).is_symlink())
            targets.append(pathlib.Path(root))
            targets.extend(pathlib.Path(root, name) for name in sorted(files) if not pathlib.Path(root, name).is_symlink() and name not in protected and not any(fnmatch.fnmatch(name, pattern) for pattern in protected_patterns))
targets.append(images)
targets.extend(sorted(path for path in images.glob('*qrcode.png') if path.is_file() and not path.is_symlink()))
for path in targets:
    assert path.resolve() == path and (path.is_relative_to(release) or path.parent == images or path == images)
    assert '\n' not in str(path) and '\r' not in str(path)
if os.environ['PARTSMALL_ACCESS_MODE'] == 'check':
    print(json.dumps({'selected_paths': len(targets), 'source_root': str(release), 'image_root': str(images), 'operation': 'read-only'}))
    raise SystemExit(0)

backup_root = pathlib.Path('/var/backups/partsmall')
assert not backup_root.is_symlink()
backup_root.mkdir(mode=0o700, parents=True, exist_ok=True)
assert backup_root.resolve() == backup_root
backup = backup_root / ('sftp-access-' + datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%d-%H%M%S-%f'))
backup.mkdir(mode=0o700, exist_ok=False)
snapshot = backup / 'acl.before'
with snapshot.open('xb') as output:
    os.chmod(snapshot, 0o600)
    for offset in range(0, len(targets), 100):
        subprocess.run(['getfacl', '-p', '-n', '--', *map(str, targets[offset:offset + 100])], stdout=output, check=True)
contents = snapshot.read_bytes()
assert contents.count(b'# file: ') == len(targets)
manifest = {'targets': list(map(str, targets)), 'sha256': hashlib.sha256(contents).hexdigest(), 'restore': 'setfacl --restore=' + str(snapshot)}
with (backup / 'manifest.json').open('x') as output:
    os.chmod(backup / 'manifest.json', 0o600)
    json.dump(manifest, output, indent=2)

def updated_acl(text, directory):
    entries = {}
    for line in text.splitlines():
        if not line.strip() or line.startswith('#'):
            continue
        entry, _, effective = line.partition('#effective:')
        fields = entry.strip().split(':')
        key = ':'.join(fields[:-1])
        entries[key] = effective.strip() if effective else fields[-1]
    # Clip previously masked entries before widening the mask for ubuntu.
    entries['user:' + uid] = 'rwx' if directory or any('x' in value for key, value in entries.items() if not key.startswith('default:')) else 'rw-'
    entries['mask:'] = 'rwx' if directory or 'x' in entries['user:' + uid] else 'rw-'
    if directory:
        if not any(key.startswith('default:') for key in entries):
            entries.update({'default:' + key: value for key, value in list(entries.items())})
        entries['default:user:' + uid] = 'rwx'
        entries['default:mask:'] = 'rwx'
    return '\n'.join(key + ':' + value for key, value in entries.items()) + '\n'

try:
    for path in targets:
        acl = subprocess.check_output(['getfacl', '-c', '-p', '-n', '-e', '--', str(path)], text=True)
        subprocess.run(['setfacl', '--set-file=-', '--', str(path)], input=updated_acl(acl, path.is_dir()), text=True, check=True)
    print(json.dumps({'updated_paths': len(targets), 'backup': str(backup), 'snapshot_sha256': manifest['sha256'], 'owners_preserved': True}))
except BaseException:
    subprocess.run(['setfacl', '--restore=' + str(snapshot)], check=True)
    raise
PY
