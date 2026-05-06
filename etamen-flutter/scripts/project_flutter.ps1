$ErrorActionPreference = "Stop"

$flutterBin = $env:ETAMEN_FLUTTER_BIN
if ([string]::IsNullOrWhiteSpace($flutterBin)) {
    $flutterBin = "C:\DevFlutter\flutter\bin\flutter.bat"
}

if (-not (Test-Path $flutterBin)) {
    Write-Error "Flutter SDK was not found at '$flutterBin'. Set ETAMEN_FLUTTER_BIN to your Flutter 3.32+ flutter.bat path."
}

& $flutterBin @args
