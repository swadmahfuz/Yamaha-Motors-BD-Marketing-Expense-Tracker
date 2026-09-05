<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install — YMB-MET</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen flex items-center justify-center bg-[var(--surface-muted)]">
    <div class="card max-w-md w-full mx-4">
        <div class="text-center mb-6">
            <img src="{{ asset('images/yamaha-logo.jpg') }}" alt="Yamaha" class="h-10 mx-auto mb-4">
            <h1 class="text-lg font-semibold uppercase tracking-wide">YMB-MET Installer</h1>
        </div>

        @if($installed)
            <p class="text-sm text-gray-600 mb-4">Application is already installed.</p>
            <a href="{{ route('login') }}" class="btn-primary w-full text-center block">Go to login</a>
        @else
            @if(session('error'))
                <p class="text-sm text-[var(--yamaha-red)] mb-4">{{ session('error') }}</p>
            @endif
            <p class="text-sm text-gray-600 mb-4">Runs database migrations and seeds demo data. Requires MySQL database <code>ymb_met</code> configured in <code>.env</code>.</p>
            <form method="POST" action="{{ route('install.run') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Installation token</label>
                    <input type="password" name="token" class="input-field" placeholder="Default: ymb-met-install" required>
                </div>
                <button class="btn-primary w-full">Install now</button>
            </form>
        @endif
    </div>
</body>
</html>
