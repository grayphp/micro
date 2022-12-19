<?php

namespace system\console;

use system\console\cliColor;

class boot
{
    protected $currentCommand = null;
    protected $commands = [];
    function __construct($commads)
    {
        if (php_sapi_name() === 'cli') {
            $this->currentCommand = $commads;
            $this->commands = $this->register($commads);
            $this->run();
        } else {
            echo 'This script can only be run from the command line';
        }
    }
    function register($commads)
    {
        if (($commads[1] == 'serve' or $commads[1] == 'start') and !isset($commads[2])) {
            echo CliColor::color("Dev Server Started Successfully.\n", 'b_black,f_green');
            system('php -S localhost:4000 -t public');
        } elseif ($commads[1] == 'serve' or $commads[1] == 'start' and isset($commads[2])) {
            echo CliColor::color("Dev Server Started Successfully.\n", 'b_black,f_green');
            system("php -S localhost:{$commads[2]} -t public");
        }
        // register command
        $shortOptions = "c:h::m:";
        $longOptions = ['controller:', 'help', 'middleware'];
        return getopt($shortOptions, $longOptions);
    }
    function help()
    {
        // help command
        $help = "Usage: php dev [command] [options]\n\n";
        $help .= "Commands:\n";
        $help .= "  serve, start [port]  Start the development server\n";
        $help .= "  -c [name] [editor command] Create a new controller\n";
        $help .= "  -h  Display this help message\n";
        print $help;
    }
    function run()
    {
        $command = $this->commands;
        $command_array = $this->currentCommand;
        if (isset($command['h'])) {
            $this->help();
        }
        if (isset($command['c']) and isset($command_array[3])) {
            $this->makeController($command['c'], $command_array[3]);
        }
        if (isset($command['c']) and !isset($command_array[3])) {
            $this->makeController($command['c']);
        }
        if (isset($command['m']) and isset($command_array[3])) {
            $this->makeMiddleware($command['m'], $command_array[3]);
        }
        if (isset($command['m']) and !isset($command_array[3])) {
            $this->makeMiddleware($command['m']);
        }
    }
    function makeController($name, $command = null)
    {
        $file = ucfirst($name);
        $content = "<?php\n\nnamespace app\web\controller;\n\nclass {$file}\n{\n\tfunction index()\n\t{\n\t\t# code...\n\t}\n}";
        file_put_contents(__DIR__ . "/../../app/web/controller/{$file}.php", $content);
        echo "{$file} created successfully\n";
        if ($command) system($command . ' ' . __DIR__ . "/../../app/web/controller/{$file}.php");
    }
    function makeMiddleware($name, $command = null)
    {
        $file = ucfirst($name);
        $content = "<?php\n\nnamespace app\web\middleware;\n\nclass {$file}\n{\n\tfunction handle()\n\t{\n\t\t# code...\n\t}\n}";
        file_put_contents(__DIR__ . "/../../app/web/middleware/{$file}.php", $content);
        echo "{$file} created successfully\n";
        if ($command) system($command . ' ' . __DIR__ . "/../../app/web/middleware/{$file}.php");
    }
}
