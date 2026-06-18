@echo off
:: === Lancer WampServer ===
start "" "C:\wamp64\wampmanager.exe"

:: Attendre que Wamp démarre
timeout /t 8 >nul

:: Aller dans le projet Laravel
cd /d C:\wamp64\www\schoolplus

:: Lancer Laravel dans une nouvelle fenêtre
start cmd /k "php artisan serve"

:: Attendre un peu que le serveur démarre
timeout /t 3 >nul

:: Vérifier si Chrome est installé
if exist "C:\Program Files\Google\Chrome\Application\chrome.exe" (
    start "" "C:\Program Files\Google\Chrome\Application\chrome.exe" "http://127.0.0.1:8000/login"
) else if exist "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" (
    start "" "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" "http://127.0.0.1:8000/login"
) else (
    :: Sinon utiliser le navigateur par défaut
    start "" "http://127.0.0.1:8000/login"
)
