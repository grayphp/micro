<?php

declare(strict_types=1);

namespace system\console;

/**
 * Micro Framework CLI
 *
 * Entry point: the `dev` executable at the project root.
 *
 * Usage:
 *   php dev serve [port]           Start development server (default: 4000)
 *   php dev make:controller Name   Scaffold a new controller
 *   php dev make:middleware Name   Scaffold a new middleware
 *   php dev -h | --help            Show this help text
 */
final class boot
{
    public function __construct(array $argv)
    {
        if (PHP_SAPI !== 'cli') {
            echo "This script must be run from the command line.\n";
            exit(1);
        }

        $this->handle($argv);
    }

    private function handle(array $argv): void
    {
        $command = $argv[1] ?? null;

        if ($command === null || $command === '-h' || $command === '--help') {
            $this->showHelp();
            return;
        }

        match ($command) {
            'serve',           'start'            => $this->serve($argv[2] ?? '4000'),
            'make:controller', '-c'               => $this->makeController($argv[2] ?? null),
            'make:middleware'                      => $this->makeMiddleware($argv[2] ?? null),
            default                               => $this->unknownCommand($command),
        };
    }

    // -------------------------------------------------------------------------
    // Commands
    // -------------------------------------------------------------------------

    private function serve(string $port): void
    {
        if (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
            $this->error("Invalid port number: {$port}");
            exit(1);
        }

        echo CliColor::color(" Micro Framework Dev Server \n", 'b_green,f_white');
        echo CliColor::color(" Listening → http://localhost:{$port} \n\n", 'f_light_green');
        passthru("php -S localhost:{$port} -t public");
    }

    private function makeController(?string $name): void
    {
        if ($name === null) {
            $this->error("Controller name is required.\n  Usage: php dev make:controller MyController");
            exit(1);
        }

        $className = ucfirst(preg_replace('/[^a-zA-Z0-9_]/', '', $name) ?? $name);
        $path      = __DIR__ . '/../../app/web/controller/' . $className . '.php';

        if (file_exists($path)) {
            $this->error("Controller '{$className}' already exists at app/web/controller/{$className}.php");
            exit(1);
        }

        file_put_contents($path, $this->controllerStub($className));

        echo CliColor::color(" ✓ Created: app/web/controller/{$className}.php\n", 'f_green');
    }

    private function makeMiddleware(?string $name): void
    {
        if ($name === null) {
            $this->error("Middleware name is required.\n  Usage: php dev make:middleware MyMiddleware");
            exit(1);
        }

        $className = ucfirst(preg_replace('/[^a-zA-Z0-9_]/', '', $name) ?? $name);
        $path      = __DIR__ . '/../../app/web/middleware/' . $className . '.php';

        if (file_exists($path)) {
            $this->error("Middleware '{$className}' already exists at app/web/middleware/{$className}.php");
            exit(1);
        }

        file_put_contents($path, $this->middlewareStub($className));

        echo CliColor::color(" ✓ Created: app/web/middleware/{$className}.php\n", 'f_green');
    }

    private function showHelp(): void
    {
        echo CliColor::color("\n Micro Framework CLI\n\n", 'f_white');
        echo CliColor::color(" Commands:\n", 'f_yellow');
        echo "  php dev serve [port]           Start development server (default: 4000)\n";
        echo "  php dev start [port]           Alias for serve\n";
        echo "  php dev make:controller Name   Scaffold a controller class\n";
        echo "  php dev make:middleware Name   Scaffold a middleware class\n";
        echo "  php dev -h | --help            Show this help text\n\n";
    }

    private function unknownCommand(string $command): void
    {
        $this->error("Unknown command '{$command}'. Run 'php dev --help' for a list of commands.");
        exit(1);
    }

    private function error(string $message): void
    {
        echo CliColor::color(" ERROR ", 'b_red,f_white') . ' ' . $message . "\n";
    }

    // -------------------------------------------------------------------------
    // Stubs
    // -------------------------------------------------------------------------

    private function controllerStub(string $name): string
    {
        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace app\web\controller;

        use system\controller\Controller;

        class {$name} extends Controller
        {
            public function index(): void
            {
                \$this->view('welcome');
            }
        }
        PHP;
    }

    private function middlewareStub(string $name): string
    {
        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace app\web\middleware;

        class {$name}
        {
            public function handle(): void
            {
                // Inspect the request here.
                // Call abort(403) or redirect('/login') to block the request.
            }
        }
        PHP;
    }
}
