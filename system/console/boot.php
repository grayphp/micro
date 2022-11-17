<?php

namespace system\console;

use system\console\cliColor;

class boot
{
    protected $currentCommand = null;
    function __construct($commads)
    {
        if (php_sapi_name() === 'cli') {
            $this->currentCommand = $this->register($commads);
            $this->run();
        } else {
            echo 'This script can only be run from the command line';
        }
    }
    function register($commads)
    {
        if ($commads[1] == 'serve' or $commads[1] == 'start' and !isset($commads[2])) {
            echo CliColor::color("Dev Server Started Successfully.\n", 'b_black,f_green');
            system('php -S localhost:4000 -t public');
        } elseif ($commads[1] == 'serve' or $commads[1] == 'start' and isset($commads[2])) {
            system("php -S localhost:{$commads[2]} -t public");
        }
        // register command
        $shortOptions = "c:h";
        $longOptions = ['controller:', 'help'];
        return getopt($shortOptions, $longOptions);
    }
    function help()
    {
        // help command
        print "Help goes here\n";
    }
    function run()
    {
        $command = $this->currentCommand;
        if (isset($command['h'])) {
            $this->help();
        }
        if (isset($command['c'])) {
            $this->makeController($command['c']);
        }
    }
    function makeController($name)
    {
        $file = ucfirst($name);
        $content = "<?php\n\nnamespace app\web\controller;\n\nclass {$file}\n{\n\tfunction __construct()\n\t{\n\t\t# code...\n\t}\n}";
        // $file = fopen("../../app/web/controller/{$file}.php", "w");
        // fwrite($file, $content);
        // fclose($file);
        $fp = fopen(APP_ROOT . "web/controller/{$file}.php", "wb");
        fwrite($fp, $content);
        fclose($fp);
        // file_put_contents("../../app/web/controller/{$file}.php", $content);
        echo "Controller created successfully\n";
    }
}