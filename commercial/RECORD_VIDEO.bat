@echo off
REM Record Diar 360 commercial to 4K MP4
cd /d "%~dp0"

where node >nul 2>&1
if errorlevel 1 (
  echo Node.js not found. Install from https://nodejs.org/
  pause
  exit /b 1
)

if not exist "node_modules\playwright" (
  echo Installing Playwright...
  call npm install
  echo.
  echo Installing browser...
  call npx playwright install chromium
  echo.
)

echo Starting 4K video recording...
echo Full version: ~3 min recording + ~2-5 min encoding
echo For faster run, use: set SHORT=1 ^& node record-to-video.js
echo.
node record-to-video.js

pause
