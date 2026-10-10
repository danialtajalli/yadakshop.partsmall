# PhpStorm SFTP Configuration Fixed

## Request

Fix PhpStorm's SFTP deployment configuration and explain how to upload changes
from PhpStorm. No upload or production deployment was requested for execution.

## Backup and Safety

Fresh copies of `.idea/deployment.xml`, `.idea/webServers.xml`, and
`.idea/sshConfigs.xml` were taken before changing settings and verified with
matching SHA-256 hashes. Protected backup location:
`C:/Users/BABAK/Documents/partsmall-config-backups/2026-10-10-151016`.
Inherited permissions were removed and owner-only access granted. Earlier
configuration backups were retained. No backup contents or credentials were
printed or included in this report.

## Changes Completed

Earlier external XML edits were overwritten by the running IDE. This time the
configuration was changed through PhpStorm's live Settings dialogs and saved
with Apply. No project was closed or process killed; unrelated projects stayed
open and unchanged. This supersedes the pending PhpStorm status in earlier
SFTP reports.

- Existing project-only deployment profile `sftp` remains the default. Its IDs
  were preserved rather than creating dangling references.
- SSH connection: `ubuntu@130.185.122.197:22`, key-pair authentication using the
  existing dedicated `partsmall_server_ed25519` key. The IDE serializes its path
  as `$USER_HOME$/.ssh/partsmall_server_ed25519` and omits the default KEY_PAIR
  auth attribute. The active connection no longer uses password authentication.
- Remote root: `/var/www/partsmall-releases/production-20261008`.
- Local project root maps to deployment path `/` relative to that remote root,
  replacing the incorrect chroot-local `/project` mapping.
- Existing exclusion defaults were preserved and expanded. New exclusions cover
  environment files, IDE metadata, dependencies, runtime storage/caches/uploads,
  generated build output, reports, and database/archive backups. Environment
  templates also match `.env*`; transfer them only through a separately reviewed
  deployment workflow.
- Live Options show automatic upload set to Never, target-item deletion
  unchecked, and automatic remote deletion disabled. Upload/deletion confirmations
  remain enabled. Existing defaults are omitted from serialized XML where normal.

No shared SSH configuration, other project's deployment profile, server chroot,
permissions, Nginx configuration, or production service was changed.

## Verification

- PhpStorm SSH Configurations Test Connection: successfully connected to
  `ubuntu@130.185.122.197:22` with the dedicated key.
- PhpStorm deployment Test SFTP Connection: successfully connected to the server.
- Native read-only inspection reconfirmed the production MySQL Compose working
  directory as `/var/www/partsmall-releases/production-20261008`.
- All three XML files parsed. IDs, host, user, port, key path, project-root mapping,
  and effective destination passed checks after Apply and subsequent IDE saves.
- Thirty-two exclusion patterns are saved. Twenty-five protected-path checks
  and eight representative source-path checks passed. `config/cache.php` and
  other source files remain uploadable despite runtime directory exclusions.
- `git diff --check` passed. No transfers, deletes, DB changes, builds, or
  container restarts were performed.

The settings were already applied. Later attempts to close the Settings dialog
were safely refused when PhpStorm no longer held foreground focus; no input was
sent to another application. There is no need to restart PhpStorm for this fix.

## Upload Steps

1. In PhpStorm's Project pane, select the project root for all non-excluded
   sources, or select specific changed files/folders for a smaller upload.
2. Right-click and choose Deployment, then Upload to `sftp` (or Upload to and
   select `sftp`). Review the upload confirmation.
3. Inspect the File Transfer tool window for completion and errors. Keep
   deletion/automatic upload disabled and do not override exclusions for secrets
   or persistent runtime data.

Sources land directly in the verified production release folder. Uploading
does not rebuild the running Docker images. A controlled publication and the
Redis queue handoff described in `docker/ops/REMEDIATION.md` remain separate.

Reference: [PhpStorm upload documentation](https://www.jetbrains.com/help/phpstorm/uploading-and-downloading-files.html).
