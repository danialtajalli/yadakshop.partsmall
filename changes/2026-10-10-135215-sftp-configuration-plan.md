# Local SFTP Configuration: Inspection and Proposed Changes

## Request

Correct the local SFTP upload destination and explain the changes before
implementing them. Configuration changes are deferred until the user reviews
the plan. This report records inspection only.

## Verified Findings

- VS Code configuration: `.vscode/sftp.json`; SFTP to `partsmall.ir:22` as
  `partsmallftp`, remote path `/project`, upload-on-save disabled.
- PhpStorm deployment: `.idea/deployment.xml`, profile `sftp`, maps the project
  root to `/project`. Related connection files are `.idea/webServers.xml` and
  `.idea/sshConfigs.xml`.
- Effective SSH settings for `partsmallftp` specify chroot
  `/srv/sftp/partsmall` and `internal-sftp -d /project`.
- Mount inspection confirms `/srv/sftp/partsmall/project` is a bind mount of
  `/var/www/partsmall`, not the active Docker production release.
- Production MySQL Compose working-directory label still identifies
  `/var/www/partsmall-releases/production-20261008`.
- Inspection used the previously configured `partsmall-server` SSH alias.
  No password or private-key contents were printed or saved in this report.

## Proposed Implementation

1. Preserve local SFTP/deployment/SSH configuration backups outside the
   repository, protecting them because existing settings may contain secrets.
2. Configure the Partsmall production connection for `ubuntu@130.185.122.197`
   using the existing dedicated SSH key, not a stored plaintext password.
   A path-only change on the chrooted account cannot reach the release directory.
3. Align VS Code and PhpStorm mappings so the local project root uploads to
   `/var/www/partsmall-releases/production-20261008`; preserve unrelated profiles.
4. Keep automatic uploads and destructive synchronization disabled. Exclude
   local secrets, IDE/Git metadata, dependency directories, runtime storage,
   bootstrap caches, and known database/archive backups. Preserve remote
   environment files, uploads, and generated runtime data.
5. Parse the JSON/XML and perform read-only connection/path checks. Do not
   upload, rebuild, restart containers, or change server permissions/chroots.

## Outcome and Limitations

No local SFTP, PhpStorm, or server configuration was changed. No files were
uploaded or deleted. Only this required report was created.

Uploading source to the current release directory is not a deployment: the
running containers use built images. A reviewed build/recreation and safe queue
transition remain separate work. Future releases require updating the mapping
to their verified directory; this date-specific path is not a permanent alias.
