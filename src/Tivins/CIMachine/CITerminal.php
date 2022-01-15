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
        $cases = implode(', ' ,
            array_map(
                fn(Level $case) => "$case->value ($case->name)",
                Level::cases()));

        $phpExtensions = implode(', ' ,
            array_map(
                fn(PHPExtension $case) => $case->value,
                PHPExtension::cases()));

        return <<<EOF

    Usage:
    
        cim --uri <uri> [options]
        cim --file <uri> [options]

    General options :

        --help, -h                      Display this help.

        --verbose, -v <mode>            Verbose level : 
                                        $cases.

    Output options : 
    
        --output, -o <directory>        Define the output directory. 
                                        The tag `[uid]` will be replaced by location ID.
                                        Default is "/tmp/cim/[uid]".
                                        
        --compress                      Build a .tar.gz instead of separated files.
        
        --docker-registry <host:port>   Specify a registry to save images.
                                        Default is not set.

    Input options :

        --file, -f <file>               Specify a configuration file. Argument overrides. 
        
        --env, -e <vars>                Environment variables.
                                        ex: "DB_NAME=test DB_USER=travis DB_PASS= DB_HOST=127.0.0.1"

    Machine options (build-time) :

        --php, -p <phpvers>             PHP version, ex: "8.1" or "latest". Default is "latest".
                                        See https://hub.docker.com/_/php?tab=tags&page=1&name=fpm

        --php-modules <modules>         Coma separated list of required PHP modules.
                                        Allowed values are : $phpExtensions
                                        Ex: "gd,imagick,pdo"

        --php-modules-dis <mods>        Coma separated list of PHP modules that should be not enabled.

        --database, -d <type>           Database server type : "mysql", "mariadb". Default is "mysql".

        --db-version <version>          Database server version. Default is "latest".
        
    Repository options (run-time) :

        --uri, -u <uri>                 [required] URI of the repository to check.
                                        It could be a local directory or a remote URI.

        --branch, -b <branch>           Branch. Default is "default".

        --commit, -c <commit>           Commit ID. Default is "HEAD". 

    Examples:
        
        cim --uri https://github.com/tivins/ci-example-1.git --php "7.4"
        
        cim --uri https://github.com/tivins/ci-example-1.git --php "8.1" --branch "test-php-8-1"
        
        cim --uri https://github.com/tivins/database.git \
            --php "7.3.22" --php-modules="pdo" --php-modules-dis="mysqli" \
            --database "mysql" --db-version "7"
            
        # configuration from local file
        cim --file /path/to/ci-project.json
        
        # override file branch
        cim --file /path/to/ci-project.json -b "my_branch"
        
        # configuration from remote file
        cim --file https://example.com/git/ci-project.json


EOF;
    }

    public function __construct()
    {
        $this->options = $this
            ->add(new OptionArg('f', true, 'file'))
            ->add(new OptionArg('u', true, 'uri'))
            ->add(new OptionArg('b', true, 'branch'))
            ->add(new OptionArg('c', true, 'commit'))
            ->add(new OptionArg('p', true, 'php'))
            ->add(new OptionArg('v', true, 'verbose'))
            ->add(new OptionArg('o', true, 'output'))
            ->add(new OptionArg('d', true, 'database'))
            ->add(new OptionArg(null, true, 'db-version'))
            ->add(new OptionArg('e', true, 'env'))
            ->add(new OptionArg('h', long: 'help'))
            ->parse();
    }

    public function getOption(string $name, mixed $default = null): mixed
    {
        return $this->options[$name] ?? $default;
    }

    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }
}