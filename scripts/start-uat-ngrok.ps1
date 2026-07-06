# Jalankan UAT dengan ngrok — inventaris-pupuk
# Setup sekali: salin authtoken ke file .ngrok-token di root proyek
# Dapatkan token: https://dashboard.ngrok.com/get-started/your-authtoken

$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $ProjectRoot

$TokenFile = Join-Path $ProjectRoot ".ngrok-token"
$Port = 8000

function Test-PortListening([int]$Port) {
    $conn = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue
    return $null -ne $conn
}

# --- Authtoken ---
if (-not (Test-Path $TokenFile)) {
    Write-Host ""
    Write-Host "File .ngrok-token belum ada." -ForegroundColor Yellow
    Write-Host "1. Daftar gratis: https://dashboard.ngrok.com/signup"
    Write-Host "2. Salin authtoken: https://dashboard.ngrok.com/get-started/your-authtoken"
    Write-Host "3. Buat file: d:\inventaris-pupuk\.ngrok-token"
    Write-Host "   Isi file hanya dengan token (satu baris, tanpa spasi)."
    Write-Host ""
    exit 1
}

$token = (Get-Content $TokenFile -Raw).Trim()
if ($token.Length -lt 10) {
    Write-Host "Token di .ngrok-token tidak valid." -ForegroundColor Red
    exit 1
}

Write-Host "Memasang authtoken ngrok..." -ForegroundColor Cyan
& ngrok config add-authtoken $token | Out-Null

# --- Laravel serve ---
if (-not (Test-PortListening $Port)) {
    Write-Host "Menjalankan php artisan serve (port $Port)..." -ForegroundColor Cyan
    Start-Process -WindowStyle Minimized -FilePath "php" -ArgumentList "artisan", "serve", "--host=127.0.0.1", "--port=$Port" -WorkingDirectory $ProjectRoot
    Start-Sleep -Seconds 3
    if (-not (Test-PortListening $Port)) {
        Write-Host "Gagal memulai Laravel di port $Port." -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "Laravel sudah berjalan di port $Port." -ForegroundColor Green
}

# --- Matikan ngrok lama di port 4040 jika ada ---
Get-Process -Name "ngrok" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 1

Write-Host "Menjalankan ngrok http $Port ..." -ForegroundColor Cyan
Start-Process -WindowStyle Minimized -FilePath "ngrok" -ArgumentList "http", "$Port", "--log=stdout" -WorkingDirectory $ProjectRoot

$publicUrl = $null
for ($i = 0; $i -lt 20; $i++) {
    Start-Sleep -Seconds 1
    try {
        $tunnels = (Invoke-RestMethod -Uri "http://127.0.0.1:4040/api/tunnels" -TimeoutSec 3).tunnels
        $publicUrl = ($tunnels | Where-Object { $_.public_url -like "https://*" } | Select-Object -First 1).public_url
        if ($publicUrl) { break }
    } catch {
        # tunggu ngrok siap
    }
}

if (-not $publicUrl) {
    Write-Host "Ngrok tidak merespons. Periksa authtoken atau jalankan manual: ngrok http $Port" -ForegroundColor Red
    exit 1
}

# --- Update APP_URL ---
$envFile = Join-Path $ProjectRoot ".env"
if (Test-Path $envFile) {
    $content = Get-Content $envFile -Raw
    if ($content -match "(?m)^APP_URL=.*$") {
        $content = $content -replace "(?m)^APP_URL=.*$", "APP_URL=$publicUrl"
    } else {
        $content += "`nAPP_URL=$publicUrl`n"
    }
    Set-Content -Path $envFile -Value $content.TrimEnd() -NoNewline
    & php artisan config:clear | Out-Null
    Write-Host "APP_URL di .env diperbarui ke: $publicUrl" -ForegroundColor Green
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  UAT SIAP - bagikan URL ini:" -ForegroundColor Green
Write-Host "  $publicUrl" -ForegroundColor White
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Login uji:" -ForegroundColor Cyan
Write-Host "  Admin : admin@example.com / password"
Write-Host "  Kasir : kasir@example.com / password"
Write-Host ""
Write-Host "Penting: jangan tutup Laravel dan ngrok; PC harus nyala." -ForegroundColor Yellow
Write-Host 'Dashboard ngrok: http://127.0.0.1:4040' -ForegroundColor DarkGray
