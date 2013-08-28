@echo off
cd %~dp0

php update_i18n.php

if errorlevel 1 pause
echo.
