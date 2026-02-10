@echo off
REM Extract audio from video for Diar 360 commercial
REM Requires FFmpeg: https://ffmpeg.org/download.html (or: winget install FFmpeg)

set "VIDEO=%USERPROFILE%\Downloads\Three-Sixty-Property-Group-Corporate-Vid_Media_pR2UOSYgXmw_001_1080p.mp4"
set "OUTPUT=%~dp0..\assets\audio\background.mp3"

if not exist "%VIDEO%" (
  echo Video not found: %VIDEO%
  echo Edit this script to set the correct path.
  pause
  exit /b 1
)

where ffmpeg >nul 2>&1
if errorlevel 1 (
  echo FFmpeg not found. Please install it first:
  echo   Option 1: winget install FFmpeg
  echo   Option 2: Download from https://ffmpeg.org/download.html
  echo   Option 3: Extract ffmpeg.exe to a folder in your PATH
  pause
  exit /b 1
)

echo Extracting audio from video...
ffmpeg -y -i "%VIDEO%" -vn -acodec libmp3lame -q:a 2 "%OUTPUT%"

if exist "%OUTPUT%" (
  echo.
  echo Done! Audio saved to: %OUTPUT%
  echo Run COPY_TO_USB.bat to include it in the portable version.
) else (
  echo Extraction failed.
)

pause
