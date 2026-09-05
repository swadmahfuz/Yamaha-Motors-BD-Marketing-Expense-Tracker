<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class InstallController extends Controller
{
    public function show(): View
    {
        $installed = User::count() > 0;

        return view('install.index', compact('installed'));
    }

    public function run(Request $request)
    {
        if (User::count() > 0) {
            return redirect()->route('login')->with('error', 'Application is already installed.');
        }

        $token = config('app.install_token', env('INSTALL_TOKEN', 'ymb-met-install'));

        if ($request->input('token') !== $token) {
            return back()->with('error', 'Invalid installation token.');
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
            Artisan::call('storage:link');

            File::put(storage_path('installed'), now()->toIso8601String());

            return redirect()->route('login')->with('success', 'Installation complete. Use demo credentials from README.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Installation failed: '.$e->getMessage());
        }
    }
}
