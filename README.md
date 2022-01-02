# CI-Machine

CI-Machine is a sandboxed environment for PHP Continuous Integration.

### Requirement

* PHP 8.1
* [tivins/php-common](https://github.com/tivins/php-common)
* [docker](https://www.docker.com/)

## Usage

```shell

    Usage: 
        cim --uri &lt;uri&gt; [options]

    General options :
        -h, --help                  Display this help.
        -v, --verbose &lt;mode&gt;        Verbose level : 
                                    0 (NONE), 1 (DANGER), 2 (WARNING), 3 (SUCCESS), 4 (INFO), 5 (DEBUG).

    Machine options (build-time) :
        -p, --php &lt;phpvers&gt;         PHP version, ex: &quot;8.1&quot; or &quot;latest&quot;. Default is &quot;lastest&quot;.
                                    See https://hub.docker.com/_/php?tab=tags&amp;page=1&amp;name=fpm
        
    Repository options (run-time) :
        -u, --uri &lt;uri&gt;             [required] URI of the repository to check.
        -b, --branch &lt;branch&gt;       Branch. Default is &quot;default&quot;.
        -c, --commit &lt;commit&gt;       Commit ID. Default is &quot;HEAD&quot;. 

    Examples:
        cim --uri https://github.com/tivins/ci-example-1.git --php &quot;7.4&quot;
        cim --uri https://github.com/tivins/ci-example-1.git --php &quot;8.1&quot; --branch &quot;test-php-8-1&quot;


```
