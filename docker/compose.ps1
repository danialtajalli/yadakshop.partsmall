param(
    [switch]$Production
)

$ErrorActionPreference = 'Stop'
$ComposeArguments = $args
$projectRoot = Split-Path -Parent $PSScriptRoot
$defaultEnv = if ($Production) { '.env.production' } else { '.env.docker' }
$defaultProject = if ($Production) { 'partsmall-prod' } else { 'partsmall-dev' }
$envFile = if ($env:DOCKER_ENV_FILE) { $env:DOCKER_ENV_FILE } else { $defaultEnv }
$projectName = if ($env:COMPOSE_PROJECT_NAME) { $env:COMPOSE_PROJECT_NAME } else { $defaultProject }
$overlay = if ($Production) { 'compose.prod.yaml' } else { 'compose.dev.yaml' }
$composeOptions = @('--env-file', $envFile, '--project-name', $projectName, '-f', 'compose.yaml', '-f', $overlay)
$previousDockerEnvFile = $env:DOCKER_ENV_FILE

Push-Location -LiteralPath $projectRoot
try {
    $env:DOCKER_ENV_FILE = $envFile
    & docker compose @composeOptions @ComposeArguments
    $composeExitCode = $LASTEXITCODE
} finally {
    $env:DOCKER_ENV_FILE = $previousDockerEnvFile
    Pop-Location
}
exit $composeExitCode
