<?php

namespace Tivins\CIMachine;

use Tivins\Core\Log\Level;

class CICli
{
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

    Machine options (build-time) :
        -p, --php <phpvers>         PHP version, ex: "8.1" or "latest". Default is "lastest".
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
}