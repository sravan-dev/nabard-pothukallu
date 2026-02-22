@echo off
echo ===============================
echo     Git Auto Update Script
echo ===============================

cd /d %~dp0

echo.
echo Adding changes...
git add .

echo.
set msg=Auto update on %date% at %time%
echo Committing with message: %msg%
git commit -m "%msg%"

echo.
echo Pushing to GitHub...
git push origin main

echo.
echo Done.
pause
