param(
    [switch]$Production
)

$ErrorActionPreference = 'Stop'
$ComposeArguments = $args
$projectRoot = Split-Path -Parent $PSScriptRoot
$composeOptions = @('--env-file', '.env.docker', '-f', 'compose.yaml')
if (-not $Production) {
    $composeOptions += @('-f', 'compose.dev.yaml')
}

Push-Location -LiteralPath $projectRoot
try {
    & docker compose @composeOptions @ComposeArguments
    $composeExitCode = $LASTEXITCODE
} finally {
    Pop-Location
}
exit $composeExitCode
