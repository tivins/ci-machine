# CI-Machine

CI-Machine is a sandbox environment for PHP Continuous Integration.

### Requirement

* PHP >= 8.1
  * ext-simplexml
* [tivins/php-common](https://github.com/tivins/php-common) *(via composer)*
* [docker](https://www.docker.com/)
* [docker compose](https://docs.docker.com/compose/install/)

## Usage

```shell

    Usage:
    
        cim --uri <uri> [options]

    General options :

        --help, -h                      Display this help.

        --verbose, -v <mode>            Verbose level : 
                                        0 (NONE), 1 (DANGER), 2 (WARNING), 3 (SUCCESS), 4 (INFO), 5 (DEBUG).

    Output options : 
    
        --output, -o <directory>        Define the output directory. 
                                        The tag `[uid]` will be replaced by location ID.
                                        Default is "/tmp/cim/[uid]".
                                        
        --compress                      Build a .tar.gz instead of separated files.

    Input options :

        --file, -f                      Specify a configuration file. Argument overrides. 

    Machine options (build-time) :

        --php, -p <phpvers>             PHP version, ex: "8.1" or "latest". Default is "latest".
                                        See https://hub.docker.com/_/php?tab=tags&page=1&name=fpm

        --php-modules <modules>         Coma separated list of required PHP modules.
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

    Output :
    
        out/
        └── build_id                # fingerprint from input + config.
            ├── ci-config.json      # host configuration.
            ├── ci-history.json     # processes output (stdout, stderr).
            ├── ci-input.json       # input data.
            ├── clover.xml          # coverage report.
            ├── phpunit-logs        # phpunit report.
            └── repository          # clean repository (clone only).

    Configuration file :
    
    env:
    jobs:
      - job01:
        php:
          version: "8.1"
          modules: "xdebug,pdo"
        db:
          type: "mysql"
          version: "latest"

```
