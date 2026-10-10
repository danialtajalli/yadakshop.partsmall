# Agent Server Policies

## Request

Create project agent instructions requiring backups before vital database changes,
protection of unrelated files and other websites, deletion only of files confirmed
with absolute certainty to be redundant, and reports after server-related prompts.
Create the `changes/` directory. "Sever related" was interpreted as "server related."

## Target and Context

- Target: the local Partsmall repository.
- The user previously confirmed that the production Docker Compose working
  directory is `/var/www/partsmall-releases/production-20261008` using Docker
  inspection output. It was not independently inspected during this request.

## Actions and Changes

- Created root `AGENTS.md` with the requested policies and verification rules.
- Created `changes/` and this first report.
- Required future reports to distinguish observations, user-provided facts,
  assumptions, and proposed actions, and to exclude secrets.
- No server connections, server configuration changes, database changes, or file
  deletions were performed for this request.

## Backups

Not applicable: this request only adds local documentation.

## Verification and Outcome

The new files were reviewed for the requested policies and report requirements.
Application tests are not applicable to this documentation-only change.
The policies are available to future agents through the root `AGENTS.md` file.

## Remaining Work

No remaining work for this request. Server operations have not been performed.
