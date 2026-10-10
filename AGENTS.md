# Partsmall Agent Policies

These policies apply to all agents working on the Partsmall project, including
local changes and work on its servers.

## Database Changes

- Always take a fresh backup before vital database changes, including destructive
  or significant migrations, bulk updates or deletions, imports or restores over
  existing data, and changes to database users or privileges.
- Back up the affected database and any other state needed to undo the change.
  Confirm that the backup succeeded, is readable, and has a viable restore path
  before proceeding. If a usable backup cannot be taken, stop the database change
  and explain the blocker.
- Keep backups outside the application release directory and protect their
  credentials and sensitive contents. Record the backup location and verification
  in the server-work report without including secrets.

## Project Boundaries and Shared Servers

- Do not delete any file unrelated to the Partsmall project.
- Before changing shared configuration, identify the affected services and
  websites, preserve the existing configuration, and establish their working
  baseline.
- Scope configuration changes to Partsmall. Ensure that other websites continue
  working, including their routing, TLS, static assets, and required services.
- Validate configuration before applying or reloading it. After applying it,
  check Partsmall and the other affected websites. If a regression occurs,
  restore the previous configuration and verify recovery.
- If the effect on other websites cannot be established, do not apply the shared
  configuration change until the uncertainty is resolved.

## File Deletion

- Do not delete a file unless you can confirm with absolute certainty that it is
  redundant and belongs to Partsmall. Age, a filename, or an apparent duplicate
  is not sufficient evidence.
- Check the resolved absolute path, references, active deployments, service
  configuration, and backup or rollback requirements before any deletion.
- Apply these rules to recursive directory deletion and automated cleanup too.
  Do not remove Docker volumes or other persistent data as routine cleanup.
- When there is any uncertainty, keep the file and explain the uncertainty.

## Server-Work Reports

- After every server-related prompt, create a new Markdown report in the
  repository-root `changes/` directory before ending the turn. This includes
  deployment guidance, SSH work, inspections, database work, configuration work,
  and requests that fail or are blocked without changing the server.
- Use a unique filename such as `YYYY-MM-DD-HHMMSS-short-description.md`. Do not
  overwrite earlier reports.
- Include the request, target environment and known paths, actions taken, files
  or services changed, backup details when applicable, verification results,
  outcome, and any blockers or remaining work.
- Distinguish verified observations from user-provided facts, assumptions, and
  proposed commands. State explicitly when no server changes were made.
- Never include passwords, tokens, private keys, secret environment values, or
  sensitive database contents in reports. Redact command output as needed.
