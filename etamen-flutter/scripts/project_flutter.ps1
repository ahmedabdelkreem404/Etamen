$ErrorActionPreference = "Stop"

$workspaceRoot = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$gradleHome = $env:ETAMEN_GRADLE_USER_HOME
if ([string]::IsNullOrWhiteSpace($gradleHome)) {
    $gradleHome = Join-Path $workspaceRoot ".gradle"
}

$tempRoot = $env:ETAMEN_TEMP_DIR
if ([string]::IsNullOrWhiteSpace($tempRoot)) {
    $tempRoot = Join-Path $workspaceRoot ".tmp"
}

New-Item -ItemType Directory -Force -Path $gradleHome | Out-Null
New-Item -ItemType Directory -Force -Path $tempRoot | Out-Null

$env:GRADLE_USER_HOME = $gradleHome
$env:TEMP = $tempRoot
$env:TMP = $tempRoot

$flutterBin = $env:ETAMEN_FLUTTER_BIN
if ([string]::IsNullOrWhiteSpace($flutterBin)) {
    $flutterBin = "C:\DevFlutter\flutter\bin\flutter.bat"
}

if (-not (Test-Path $flutterBin)) {
    Write-Error "Flutter SDK was not found at '$flutterBin'. Set ETAMEN_FLUTTER_BIN to your Flutter 3.32+ flutter.bat path."
}

& $flutterBin @args
