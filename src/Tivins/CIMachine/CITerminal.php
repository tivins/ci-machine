<?php

namespace Tivins\CIMachine;

use Tivins\Core\Log\Level;
use Tivins\Core\OptionArg;
use Tivins\Core\OptionsArgs;

class CITerminal extends OptionsArgs
{
    private array $options = [];

    public function usage(): string
    {
        $cases = [];
        foreach (Level::cases() as $case) {
            $cases[] = "$case->value ($case->name)";
        }
        $cases = implode(', ', $cases);

        return <<<EOF

    Usage: 
        cim --uri <uri> [options]

    General options :
        -h, --help                  Display this help.
        -v, --verbose <mode>        Verbose level : 
                                    $cases.
        -o, --output <directory>    Define the output directory. The tag `[uid]` will be replaced by location ID.
                                    Default is "/tmp/cim/[uid]".

    Machine options (build-time) :
        -p, --php <phpvers>         PHP version, ex: "8.1" or "latest". Default is "latest".
                                    See https://hub.docker.com/_/php?tab=tags&page=1&name=fpm
        
    Repository options (run-time) :
        -u, --uri <uri>             [required] URI of the repository to check.
        -b, --branch <branch>       Branch. Default is "default".
        -c, --commit <commit>       Commit ID. Default is "HEAD". 

    Examples:
        cim --uri https://github.com/tivins/ci-example-1.git --php "7.4"
        cim --uri https://github.com/tivins/ci-example-1.git --php "8.1" --branch "test-php-8-1"


EOF;
    }

    public function __construct()
    {
        $this->options = $this
            ->add(new OptionArg('u', true, 'uri'))
            ->add(new OptionArg('b', true, 'branch'))
            ->add(new OptionArg('c', true, 'commit'))
            ->add(new OptionArg('p', true, 'php'))
            ->add(new OptionArg('v', true, 'verbose'))
            ->add(new OptionArg('o', true, 'output'))
            ->add(new OptionArg('h', long: 'help'))
            ->parse();
    }

    public function getOption(string $name, mixed $default = null): mixed {
        return $this->options[$name] ?? $default;
    }
    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }
}