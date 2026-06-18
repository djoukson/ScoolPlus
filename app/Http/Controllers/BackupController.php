<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Backup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


use Spatie\DbDumper\Databases\MySql;


class BackupController extends Controller
{
    public function index()
    {
        $backups = Backup::orderBy('created_at', 'desc')->get();

        // 🔹 Log de consultation
        logAction('Consultation', 'Affichage de la liste des sauvegardes de la base de données.');

        return view('backups.index', compact('backups'));
    }

    public function create()
    {
        $timestamp = now()->format('Y_m_d_His');
        $filename  = "backup_{$timestamp}.sql";
        $zipPassword = 'Schoolplus12398$$'; // 🔑 Change le mot de passe ici

        /* ===============================
           📦 1. Sauvegarde serveur
        =============================== */
        $projectDir  = storage_path('app/backups/schoolplus/dbcript');
        $projectPath = $projectDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($projectDir)) {
            mkdir($projectDir, 0755, true);
        }

        /* ===============================
           💽 2. Disque D
        =============================== */
        $diskDDir  = 'D:/backups/schoolplus/dbcript';
        $diskDPath = $diskDDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($diskDDir)) {
            @mkdir($diskDDir, 0755, true);
        }

        /* ===============================
           🔌 3. Détection clé USB
        =============================== */
        $usbDrives = ['E:', 'F:', 'G:', 'H:'];
        $usbPath   = null;
        foreach ($usbDrives as $drive) {
            if (is_dir($drive . DIRECTORY_SEPARATOR)) {
                $usbPath = $drive . DIRECTORY_SEPARATOR . 'schoolplus_backup';
                break;
            }
        }

        $successTables = [];
        $failedTables  = [];
        $diskDStatus   = 'Disque D non disponible';
        $usbStatus     = 'Clé USB non détectée';

        try {
            $pdo = DB::connection()->getPdo();
            $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            $sqlDump = '';

            foreach ($tables as $table) {
                try {
                    $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n";
                    $create = $pdo->query("SHOW CREATE TABLE `$table`")
                        ->fetch(\PDO::FETCH_ASSOC);
                    $sqlDump .= $create['Create Table'] . ";\n\n";

                    $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(\PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                        $columns = array_map(fn($c) => "`$c`", array_keys($row));
                        $values  = array_map(fn($v) => $pdo->quote($v), array_values($row));
                        $sqlDump .= "INSERT INTO `$table` (" . implode(',', $columns) . ") VALUES (" . implode(',', $values) . ");\n";
                    }

                    $sqlDump .= "\n\n";
                    $successTables[] = $table;

                } catch (\Exception $e) {
                    $failedTables[$table] = $e->getMessage();
                    continue;
                }
            }

            // Écriture du SQL temporaire
            file_put_contents($projectPath, $sqlDump);

            /* ===============================
               Créer un 7-Zip protégé par mot de passe
            =============================== */
            $zipFile = $projectDir . DIRECTORY_SEPARATOR . $filename . '.7z';

            $sevenZip = '"C:\Program Files\7-Zip\7z.exe"';

            // Vérifie que 7z.exe est installé et accessible via PATH
            $command = "$sevenZip a -t7z \"$zipFile\" \"$projectPath\" -p$zipPassword -mhe=on";

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                return redirect()->back()->with('error', 'Erreur lors de la création du 7-Zip protégé.');
            }

            // Supprimer le SQL non chiffré
            @unlink($projectPath);

            /* ===============================
               Copie vers Disque D
            =============================== */
            if (is_dir('D:/')) {
                if (@copy($zipFile, $diskDDir . DIRECTORY_SEPARATOR . $filename . '.7z')) {
                    $diskDStatus = 'Sauvegarde copiée sur le disque D';
                } else {
                    $diskDStatus = 'Disque D détecté mais copie échouée';
                }
            }

            /* ===============================
               Copie vers clé USB
            =============================== */
            if ($usbPath) {
                if (!file_exists($usbPath)) {
                    @mkdir($usbPath, 0755, true);
                }

                if (@copy($zipFile, $usbPath . DIRECTORY_SEPARATOR . $filename . '.7z')) {
                    $usbStatus = 'Sauvegarde copiée sur clé USB';
                } else {
                    $usbStatus = 'Clé détectée mais copie échouée';
                }
            }

            /* ===============================
               Enregistrement BDD
            =============================== */
            if (count($successTables) > 0) {
                DB::table('backups')->insert([
                    'filename'   => $filename . '.7z',
                    'path'       => $zipFile,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Message final
            $message  = "Sauvegarde terminée.\n";
            $message .= "📦 Tables OK : " . count($successTables) . "\n";
            $message .= "❌ Tables échouées : " . count($failedTables) . "\n";
            $message .= "💽 Disque D : " . $diskDStatus . "\n";
            $message .= "🔌 USB : " . $usbStatus;

            return redirect()->back()->with([
                'success' => $message,
                'backup_details' => [
                    'success' => $successTables,
                    'failed'  => $failedTables
                ]
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with(
                'error',
                'Erreur générale de sauvegarde : ' . $e->getMessage()
            );
        }
    }






    // **Télécharger une sauvegarde**
    public function download($id)
    {
        $backup = Backup::findOrFail($id);

        // 🔹 Log de téléchargement
        logAction('Téléchargement', "Téléchargement de la sauvegarde : {$backup->filename}");

        return response()->download($backup->path, $backup->filename);
    }
}
