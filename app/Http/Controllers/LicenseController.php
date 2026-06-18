<?php
namespace App\Http\Controllers;

//use Faker\Core\File;
use Illuminate\Http\Request;
use App\Models\License;
use Illuminate\Support\Facades\File;   // ✅ BONNE CLASSE
use ZipArchive;

class LicenseController extends Controller
{

    public function index()
    {
        $licenses = License::all();
        return view('admin.licenses.index', compact('licenses'));
    }

    public function create()
    {
        return view('admin.licenses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'duration_years' => 'nullable|numeric|min:0',
            'type' => 'required|string'
        ]);

        if($request->type === 'lifetime') {
            $years = null;
            $expires = null;
        } elseif($request->type === 'test') {
            $years = 0.25; // 3 mois = 0.25 an
            $expires = now()->addMonths(3);
        } else {
            $years = $request->duration_years;
            $expires = now()->addMonths($years * 12);
        }

        $key = generateLicenseKey($request->school_name, $years);

        License::create([
            'school_name' => $request->school_name,
            'license_key' => $key,
            'duration_years' => $years,
            'expires_at' => $expires,
            'status' => 'active'
        ]);

        return redirect()->route('admin.licenses.index')->with('success', 'Licence créée avec succès !');
    }


    public function edit(License $license)
    {
        return view('admin.licenses.edit', compact('license'));
    }

    public function update(Request $request, License $license)
    {
        $request->validate([
            'status' => 'required|in:active,expired,revoked'
        ]);

        $license->update([
            'status' => $request->status
        ]);

        return redirect()->route('admin.licenses.index')->with('success', 'Licence mise à jour !');
    }

    public function destroy(License $license)
    {
        $license->delete();
        return redirect()->route('admin.licenses.index')->with('success', 'Licence supprimée !');
    }

    public function download($id)
    {
        $license = License::findOrFail($id);

        // ---- PAYLOAD LICENCE ----
        $payload = [
            'school_name' => $license->school_name,
            'license_key' => $license->license_key,
            'expires_at'  => $license->expires_at,
            'issued_at'   => now()->toDateTimeString(),
            'activated' => false,
        ];

        // ---- FICHIER LICENCE ----
        $encrypted = encrypt(json_encode($payload));

        $licFile = storage_path('app/license_init.lic');
        File::put($licFile,$encrypted);

        // ---- FICHIER BAT ----
        $batContent = <<<BAT
@echo off
php activate.php
pause
BAT;

        $batFile = storage_path("app/activate.bat");
        File::put($batFile,$batContent);

        // ---- ZIP FINAL ----
        $zipName = "Licence_{$license->school_name}.zip";
        $zipPath = storage_path("app/$zipName");

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE|ZipArchive::OVERWRITE);

        $zip->addFile($licFile, 'license_init.lic');
        $zip->addFile($batFile, 'activate.bat');

        $zip->close();

        // Nettoyage
        unlink($licFile);
        unlink($batFile);

        // Téléchargement
        return response()->download($zipPath)->deleteFileAfterSend();
    }
}
