# CI-Machine

CI-Machine is a dynamic sandbox environment for PHP (or maybe more) Continuous Integration.

### Requirement

* PHP >= 8.1
  * ext-simplexml
  * ext-yaml
* [tivins/php-common](https://github.com/tivins/php-common) *(via composer)*
* [docker](https://www.docker.com/)
* [docker compose](https://docs.docker.com/compose/install/)

## Usage

```shell
{!cimUsage!}
```

Output :

```
out/
└── {build_id}/                   # fingerprint from input + config.
    ├── docker/                   # re-renable docker environment.
    │   ├── env/                  # generated file 
    │   │   ├── Dockerfile        # PHP environment 
    │   │   └── test.php          # report machine status (versions, configs, ...) 
    │   ├── volume/               # clean repository (clone only).
    │   └── docker-composer.yml   # generated yml file 
    ├── ci-config.json            # host configuration.
    ├── ci-history.json           # processes output (stdout, stderr).
    ├── ci-input.json             # input data.
    ├── clover.xml                # coverage report.
    └── phpunit-logs              # phpunit report.
```

Configuration file :

{!configYml!}