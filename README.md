# CI-Machine

CI-Machine is a sandboxed environment for PHP Continuous Integration.

### Requirement

* PHP 8.1
* [tivins/php-common](https://github.com/tivins/php-common)
* [docker](https://www.docker.com/)

## Usage

```shell

    Usage: 
        cim --uri <uri> [options]

    General options :
        -h, --help                  Display this help.
        -v, --verbose <mode>        Verbose level : 
                                    0 (NONE), 1 (DANGER), 2 (WARNING), 3 (SUCCESS), 4 (INFO), 5 (DEBUG).

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


```
