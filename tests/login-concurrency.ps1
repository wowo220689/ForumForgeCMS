param([Parameter(Mandatory=$true)][string]$FixtureDirectory, [Parameter(Mandatory=$true)][string]$Php, [Parameter(Mandatory=$true)][string]$ExtensionDirectory)
$ErrorActionPreference = 'Stop'
$worker = Join-Path $PSScriptRoot 'fixture-security.php'
$arguments = @('-n','-d',"extension_dir=$ExtensionDirectory",'-d','extension=pdo_sqlite','-d','extension=mbstring',$worker,$FixtureDirectory)
& $Php @arguments reset
if ($LASTEXITCODE -ne 0) { throw 'Cannot reset test fixture' }
$processes = @()
for ($i=0; $i -lt 16; $i++) {
    $start = [System.Diagnostics.ProcessStartInfo]::new()
    $start.FileName = $Php
    $start.UseShellExecute = $false
    $start.CreateNoWindow = $true
    $start.RedirectStandardOutput = $true
    $start.RedirectStandardError = $true
    foreach ($argument in $arguments) { $start.ArgumentList.Add($argument) }
    $processes += [System.Diagnostics.Process]::Start($start)
}
$allowed = 0
$blocked = 0
foreach ($process in $processes) {
    $output = $process.StandardOutput.ReadToEnd()
    $errorOutput = $process.StandardError.ReadToEnd()
    $process.WaitForExit()
    if ($process.ExitCode -ne 0) { throw "Worker failed: $errorOutput $output" }
    if ($output.Trim() -eq 'ALLOWED') { $allowed++ }
    elseif ($output.Trim() -eq 'BLOCKED') { $blocked++ }
    else { throw "Unexpected worker response: $output" }
    $process.Dispose()
}
& $Php @arguments reset
if ($allowed -ne 8 -or $blocked -ne 8) { throw "Incorrect concurrency result: $allowed allowed, $blocked blocked" }
Write-Output 'PASS: 16 parallel login reservations, exactly 8 allowed and 8 blocked.'
