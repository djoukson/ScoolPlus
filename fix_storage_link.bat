@echo off
setlocal

set "PROJECT_DIR=C:\wamp64\www\schoolplus"
set "LINK=%PROJECT_DIR%\public\storage"

cd /d "%PROJECT_DIR%"

echo Verification du lien symbolique Laravel storage...

:: Test si c'est déjà un lien symbolique
dir /AL "%LINK%" >nul 2>&1
if %errorlevel% equ 0 (
    echo Le lien storage existe deja et fonctionne.
    goto :eof
)

echo Lien absent ou casse. Reconstruction...

:: Supprimer ancien dossier ou faux lien
if exist "%LINK%" (
    rmdir /S /Q "%LINK%" 2>nul
)

:: Recréer via Laravel
php artisan storage:link

:: Vérification réelle
dir /AL "%LINK%" >nul 2>&1
if %errorlevel% equ 0 (
    echo SUCCES : lien storage cree correctement.
) else (
    echo ECHEC : le lien storage n'a pas pu etre cree.
    echo Lancez ce script en Administrateur.
)

pause
endlocal
