<?php
// setup_middleware.php

function writeFileSafe($path, $content) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($path, $content);
    echo "Created: $path\n";
}

// 1. EnsureTenant Middleware
$ensureTenant = <<<'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admin does not strictly require a company_id
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Verify user has an active company
        if (!$user->company_id || !$user->company || !$user->company->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your company account is inactive or not configured. Please contact platform support.'
            ]);
        }

        return $next($request);
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Http/Middleware/EnsureTenant.php', $ensureTenant);

// 2. CheckRole Middleware
$checkRole = <<<'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === 'super_admin' || in_array($user->role, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized access to this module.');
    }
}
PHP;
writeFileSafe(__DIR__ . '/app/Http/Middleware/CheckRole.php', $checkRole);

// 3. Update bootstrap/app.php
$bootstrapApp = <<<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => \App\Http\Middleware\EnsureTenant::class,
            'role'   => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
PHP;
writeFileSafe(__DIR__ . '/bootstrap/app.php', $bootstrapApp);

echo "Middleware and bootstrap/app.php configured successfully.\n";
