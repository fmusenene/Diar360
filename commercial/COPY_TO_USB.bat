@echo off
REM DIAR 360 - Copy Commercial to USB
REM Run this script to create a portable folder you can copy to a USB drive

set SOURCE=%~dp0
set TARGET=%SOURCE%Diar360_TV_Advert

echo Creating portable commercial folder...
if exist "%TARGET%" rmdir /s /q "%TARGET%"
mkdir "%TARGET%"
mkdir "%TARGET%\assets"
mkdir "%TARGET%\assets\img"
mkdir "%TARGET%\assets\img\construction"
mkdir "%TARGET%\assets\audio" 2>nul

echo Copying commercial files...
copy "%SOURCE%index.html" "%TARGET%\index.html"

echo Copying images...
copy "%SOURCE%..\assets\img\construction\*.webp" "%TARGET%\assets\img\construction\"
copy "%SOURCE%..\assets\img\construction\*.png" "%TARGET%\assets\img\construction\" 2>nul
copy "%SOURCE%..\assets\img\diar360-logo.png" "%TARGET%\assets\img\" 2>nul
copy "%SOURCE%..\assets\audio\background.mp3" "%TARGET%\assets\audio\" 2>nul

echo Updating paths in index.html for portable use...
powershell -Command "(Get-Content '%TARGET%\index.html') -replace '\.\./assets/', './assets/' | Set-Content '%TARGET%\index.html'"

echo Copying README...
copy "%SOURCE%README.md" "%TARGET%\README.txt"

echo.
echo Done! Portable folder created at:
echo %TARGET%
echo.
echo Copy the entire "Diar360_TV_Advert" folder to your USB drive.
echo Then open index.html on any computer and press F11 for fullscreen.
echo.
pause
