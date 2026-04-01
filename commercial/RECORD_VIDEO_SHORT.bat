@echo off
REM Quick record - English only (~1.5 min) - Faster for testing
cd /d "%~dp0"

if not exist "node_modules\playwright" (
  echo Run RECORD_VIDEO.bat first to install dependencies.
  pause
  exit /b 1
)

echo Recording SHORT version (English only, ~90 sec)...
set SHORT=1
node record-to-video.js
pause
