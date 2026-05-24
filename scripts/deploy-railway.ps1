# Deploy inventaris-pupuk ke Railway
# Prasyarat: akun Railway, repo GitHub sudah push, MySQL service ditambahkan di project

$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $ProjectRoot

if (-not (Get-Command railway -ErrorAction SilentlyContinue)) {
    Write-Host "Menginstal Railway CLI..." -ForegroundColor Cyan
    npm install -g @railway/cli
}

$whoami = railway whoami 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "Login Railway dulu (browser akan terbuka):" -ForegroundColor Yellow
    railway login
}

if (-not (Test-Path ".railway")) {
    Write-Host "Membuat project Railway baru..." -ForegroundColor Cyan
    railway init --name inventaris-pupuk
}

Write-Host "Menambahkan MySQL (abaikan jika sudah ada)..." -ForegroundColor Cyan
railway add --database mysql 2>$null

Write-Host "Deploy dari folder ini..." -ForegroundColor Cyan
railway up --detach

Write-Host ""
Write-Host "Setelah deploy, atur Variables di dashboard Railway (lihat railway.env.example)." -ForegroundColor Yellow
Write-Host "Generate domain: railway domain" -ForegroundColor Cyan
railway domain 2>$null

Write-Host "Buka dashboard: railway open" -ForegroundColor Cyan
