<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\License;
use Illuminate\Support\Facades\File;

class OfflineLicenseController extends Controller
{
    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Vérifier la clé dans la base ADMIN
        |--------------------------------------------------------------------------
        */

        $license = License::where('license_key',$request->license_key)
            ->where('status','active')
            ->first();

        if (!$license) {
            return back()->withErrors(['license_key' => 'Clé invalide ou désactivée.']);
        }

        /*
        |--------------------------------------------------------------------------
        | Générer la licence locale liée à la machine
        |--------------------------------------------------------------------------
        */

        $machineId = $this->getMachineId();

        $payload = [
            'school_name' => $license->school_name,
            'license_key' => $license->license_key,
            'expires_at'  => $license->expires_at,
            'machine_id'  => $machineId,
            'activated_at' => now()->toDateTimeString()
        ];

        $encrypted = encrypt(json_encode($payload));

        File::put(storage_path('app/license.lic'), $encrypted);

        return redirect('/login')->with('success','✅ Licence activée avec succès !');
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE MACHINE ID
    |--------------------------------------------------------------------------
    */
    private function getMachineId()
    {
        $cpu = php_uname();
        $disk = @disk_total_space("C:/") ?: 0;

        return hash('sha256',$cpu.$disk);
    }
}
