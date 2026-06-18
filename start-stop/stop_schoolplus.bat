@echo off
echo ===============================
echo   🔴 Fermeture de SchoolPlus et Wamp
echo ===============================

:: Fermer Laravel (php artisan serve)
taskkill /F /IM php.exe >nul 2>&1

:: Fermer WampServer (gestionnaire)
taskkill /F /IM wampmanager.exe >nul 2>&1

:: Fermer Apache (serveur web)
taskkill /F /IM httpd.exe >nul 2>&1

:: Fermer MySQL (base de données)
taskkill /F /IM mysqld.exe >nul 2>&1
taskkill /F /IM mysqld-nt.exe >nul 2>&1

echo ✅ Tous les services liés à SchoolPlus et Wamp sont fermés.
pause
