# Editor SFTP Configuration Update

## Request and Target

The user approved correcting both VS Code and PhpStorm SFTP settings.
Target: `ubuntu@130.185.122.197`, project directory
`/var/www/partsmall-releases/production-20261008`, using the dedicated existing
SSH key. No upload, deployment, container restart, or server-side configuration
change was authorized or performed.

## Backups

Four local configuration files were copied before edits into
`C:/Users/BABAK/Documents/partsmall-config-backups/2026-10-10-140009`:
`.vscode/sftp.json`, `.idea/deployment.xml`, `.idea/webServers.xml`, and
`.idea/sshConfigs.xml`. Source/copy SHA-256 checks matched. Inherited directory
permissions were removed and owner-only access granted. Backups may contain old
connection secrets and are intentionally outside the repository.

## VS Code: Completed

- `.vscode/sftp.json` now names `Partsmall Production`, connects to the server
  as `ubuntu`, uses the existing SSH private-key path, and targets the verified
  production directory instead of chroot-local `/project`.
- Removed the plaintext password from the active VS Code configuration.
- Upload-on-save, download-on-open, watcher auto-upload/auto-delete, and sync
  deletion are disabled.
- Excluded Git/IDE metadata, environment files, dependency/runtime directories,
  generated assets, reports, and database/archive backups. Environment templates
  are intentionally included in `.env*` exclusions and require separately
  reviewed handling rather than bulk SFTP upload.
- JSON/safety checks and 25 protected-path checks passed using the installed
  SFTP extension's ignore library. Representative source paths remain uploadable.
- A read-only connection using the installed VS Code extension's SSH2 client
  authenticated with the key, verified the previously trusted host key, and
  resolved/statted the target directory successfully. Nothing was uploaded.
- Native OpenSSH SFTP also successfully changed into the same directory.

## PhpStorm: Pending IDE Closure

Edits were attempted in the three `.idea` files: production root path,
project-root mapping, key-pair SSH settings, upload exclusions, and disabled
automatic uploads/deletion. Subsequent verification found all three files back
at their old settings. The running PhpStorm instance appears to be saving its
in-memory state over external edits. Therefore PhpStorm is NOT marked complete.

The user was asked to close the Partsmall project so these approved edits can be
applied and validated without competing writes. The IDE was not killed, and
configuration files were not made read-only to force changes.

## Outcome and Remaining Work

VS Code is updated and connection-tested. PhpStorm still uses the previous
`partsmallftp` connection and `/project` mapping until its project is closed and
the new settings can persist. Backups remain intact; `git diff --check` passed.
No files were uploaded/deleted on the server and no production service changed.

After both configurations are in place, reload VS Code and reopen the PhpStorm
project to load the settings. Uploading source does not rebuild the currently
running Docker images. Future releases require re-verifying/updating this
date-specific destination.
