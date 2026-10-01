$ErrorActionPreference = 'Stop'
$proyecto = Split-Path -Parent $PSScriptRoot
$php = (Get-Command php.exe -ErrorAction Stop).Source
$trabajador = Join-Path $PSScriptRoot 'telegram_notificaciones.php'
$accion = New-ScheduledTaskAction -Execute $php -Argument ('"{0}"' -f $trabajador) -WorkingDirectory $proyecto
$inicio = New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) -RepetitionInterval (New-TimeSpan -Minutes 1)
$ajustes = New-ScheduledTaskSettingsSet -MultipleInstances IgnoreNew -StartWhenAvailable -ExecutionTimeLimit (New-TimeSpan -Minutes 10)
$identidad = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name
$principal = New-ScheduledTaskPrincipal -UserId $identidad -LogonType Interactive -RunLevel Limited
$nombre = 'BeniTurs-Telegram'
if (Get-ScheduledTask -TaskName $nombre -ErrorAction SilentlyContinue) {
    throw 'La tarea BeniTurs-Telegram ya existe. Revísala antes de reemplazarla.'
}
Register-ScheduledTask -TaskName $nombre -Action $accion -Trigger $inicio -Settings $ajustes -Principal $principal -Description 'Procesa la cola de solicitudes de BeniTurs para Telegram cada minuto.' | Out-Null
Write-Output 'Tarea creada. Procesará la cola cada minuto mientras este usuario tenga sesión iniciada.'
