<?php

namespace Tivins\CIMachine;

use Tivins\Core\Proc\Command;
use Tivins\Core\Log\Logger;
use Tivins\Core\Proc\ProcBackground;
use Tivins\Core\Proc\ProcInfo;
use Tivins\Core\System\FileSys;

class CIMachine
{
    public const  ROOT_DIR            = '/box';
    public const  CLONE_DIR           = self::ROOT_DIR . '/clone';
    public const  PHP_LATEST          = '8.1.1';
    public const  DEFAULT_DIR         = '/tmp/cim/[uid]';
    private const DOCKERFILE_PHP_PATH = '/docker/env/Dockerfile';
    private const DOCKER_COMPOSE_PATH = '/docker/docker-compose.yml';

    public readonly string $uid;

    private string $phpVersion      = self::PHP_LATEST;
    private string $databaseType    = ''; // mysql
    private string $databaseVersion = ''; // latest
    private array  $envVars = [];

    private GitLocation   $location;
    private Mount         $volume;
    private DockerCompose $dockerCompose;
    private DockerFile    $dockerFile;
    private string        $directory = self::DEFAULT_DIR;
    private array         $history   = [];
    private ?Logger       $logger    = null;


    public function __construct(GitLocation $location)
    {
        $this->uid           = 'ci_' . sha1(json_encode([$location, microtime(true)]));
        $this->location      = $location;
        $this->volume        = new Mount($this->uid, self::ROOT_DIR);
        $this->dockerFile    = new DockerFile();
        $this->dockerFile->addLib('git');
        $this->dockerFile->addPHPExtension(PHPExtension::ZIP); // composer
        $this->dockerCompose = new DockerCompose($this);
    }

    public function getRealOutDir(): string
    {
        return str_replace('[uid]', $this->uid, $this->directory);
    }

    private function saveDockerComposeFile(): void
    {
        FileSys::writeFile($this->getRealOutDir() . self::DOCKER_COMPOSE_PATH, (string) $this->dockerCompose);
    }

    private function saveDockerfile(): void
    {
        $this->dockerFile->setPHPVersion($this->phpVersion);
        $this->dockerFile->addPHPExtension(PHPExtension::XDEBUG);
        $this->dockerFile->addPHPExtension(PHPExtension::PDO);

        FileSys::writeFile($this->getRealOutDir() . self::DOCKERFILE_PHP_PATH, (string) $this->dockerFile);
    }

    public function run()
    {
        FileSys::mkdir($this->getRealOutDir());
        $this->saveDockerfile();
        $this->saveDockerComposeFile();
        //
        //
        //
        $ciEnv = [
            'CI_ENV_PHP' => $this->phpVersion,
            'CI_UID'     => $this->uid,
        ];
        //
        //
        //
        chdir($this->getRealOutDir() . '/docker');
        mkdir('volume');
        //
        //
        //
        foreach ($ciEnv as $key => $value) {
            putenv($key . '=' . $value);
        }



        //
        //
        //
        $this->runCommand(new NamedCommand('Docker clean volumes',
            'docker', 'system', 'prune', '-f'));
        //
        //
        //
        $this->runCommand(new NamedCommand('Docker config',
            'docker', 'compose', 'config'));
        //
        //
        chdir($this->getRealOutDir() . '/docker');

        //
        $this->runCommand(new NamedCommand('Docker compose up',
            'docker', 'compose', 'up', '--remove-orphans', '--build', '-d'));
        //
        //
        //
        sleep(1);
        //
        //
        //
        $this->runCommand(new NamedCommand('Docker exec test.php',
            'docker', 'exec', $this->dockerCompose->getPHPContainerName(),
            'php', '/test.php'));
        //
        //
        //
        $this->runDockerCommand($this->gitCloneCommand());
        $this->runDockerCommand(new NamedCommand('Copy original repository', 'cp', '-r', 'clone', 'repository'));
        //$this->runDockerCommand(new NamedCommand('Composer install project', 'composer', 'update',  '--no-interaction'), self::CLONE_DIR);
        $this->runDockerCommand(new NamedCommand('Composer install project', 'composer', 'install', '--no-interaction'), self::CLONE_DIR);
        $this->runDockerCommand($this->getPHPUnitCommand(), self::CLONE_DIR);

        // $this->runDockerCommand(new NamedCommand('PHP version', 'php', '-v'));
        // $this->runDockerCommand(new NamedCommand('PHP version', 'php', '-m'));
        $this->runDockerCommand(new NamedCommand('PHP version', 'php', '-r', 'print_r(getenv());'));
        // $this->runDockerCommand(new NamedCommand('PHP version', 'php', '-r', 'phpinfo();'));
        // $this->runDockerCommand(new NamedCommand('Git status', 'git', 'status'), self::CLONE_DIR);

        $this->runCommand(new NamedCommand('Docker save images',
            'docker', 'image', 'save',
            '-o', $this->getRealOutDir().'/img',
            $this->dockerCompose->getPHPImageName()));

        //
        // unset
        //
        foreach ($ciEnv as $key => $value) {
            putenv($key);
        }
    }

    public function close()
    {
        $this->logger?->info('Closing...');
        // $this->removeVolume();
    }

    public function doRunBackupClose()
    {
        $this->run();
        $this->logger?->info('Backup...');
        $this->backup();
        $this->close();
    }

    private function runCommand(NamedCommand $command): ProcInfo
    {
        $this->logger->debug(__function__, $command->get());
        $proc = new ProcBackground($command->name);
        $proc->setShowStdout(true);
        $proc->setShowStderr(true);
        $this->logger?->debug(getcwd() . '$ ' . implode(' ', $command->get()));
        return $this->history[] = $proc->run($command, 100000);
    }

    private function runDockerCommand(NamedCommand $command, ?string $workDir = null): void
    {
        $command->name = '[container] ' . $command->name;
        $this->runCommand($this->dockerRunCommand($command, $workDir));
    }

    /**
     * Return a "docker build" command.
     *
     * Schema
     *      docker build --build-arg [args] -t [tag] -f [Dockerfile] .
     */
    public function dockerBuildCommand(): NamedCommand
    {
        $command = new NamedCommand('Build image', 'docker', 'build');
        $command->add('--build-arg', "PHP=$this->phpVersion");
        $command->add('-t', $this->getTagName());
        $command->add('-f', 'Dockerfile');
        $command->add('.');
        return $command;
    }

    /**
     * Run a "docker run" command.
     *
     * Schema
     *      docker run -it --rm --name [name] [tag] <command>
     *
     * @param NamedCommand $runCommand The command to run inside the container.
     * @param string|null $workDir The working directory to run the command.
     * @param Command|null $extra Extraneous command for "docker run".
     * @return NamedCommand The command to run.
     */
    public function dockerRunCommand(NamedCommand $runCommand, ?string $workDir = null, ?Command $extra = null): NamedCommand
    {
        $command = new NamedCommand($runCommand->name, 'docker', 'exec');
        //if  (!empty($this->envVars)) {
            $command->add('-e', "DB_NAME=database");
            $command->add('-e', "DB_USER=admin");
            $command->add('-e', "DB_PASS=adminpass");
            $command->add('-e', "DB_HOST=db");
                    /*
        $proc->setEnvVars([
            'DB_NAME' => 'database',
            'DB_USER' => 'admin',
            'DB_PASS' => 'adminpass',
            'DB_HOST' => 'db',
        ]);*/
        //  }
        if ($workDir) {
            $command->add('--workdir', $workDir);
        }
        $command->add($this->dockerCompose->getPHPContainerName());
        $command->addCommand($runCommand);
        return $command;
    }

    /**
     * Get the "git clone" command.
     *
     * @param int $depth
     * @return NamedCommand
     */
    public function gitCloneCommand(int $depth = 50): NamedCommand
    {
        $cmd = new NamedCommand('Clone repository',
            'git', 'clone');
        $cmd->add('-q');
        if ($depth) {
            $cmd->add('--depth=' . $depth);
        }
        $cmd->add($this->location->uri);
        if ($this->location->branch != 'default') {
            $cmd->add('-b', $this->location->branch);
        }
        $cmd->add('clone');
        return $cmd;
    }

    /**
     * @return NamedCommand
     */
    public function getPHPUnitCommand(): NamedCommand
    {
        return (new NamedCommand('Unit tests', 'vendor/bin/phpunit'))
            // ->add('--log-teamcity', '/box/phpunit-logs')
            ->add('--coverage-clover', '/box/clover.xml')
            ->add('--teamcity');
    }

    private function backup()
    {
        // $this->runDockerCommand(new NamedCommand('Delete git clone','rm', '-rf', self::CLONE_DIR));
        $dir = $this->getRealOutDir();

        // FileSys::mkdir($dir);
        // $tarCommand = new NamedCommand('Export volume', 'zip', '-r', '/backup/volume.zip', '/box/');
        // $this->runCommand($this->dockerRunCommand($tarCommand, extra: new Command('-v', $dir . ':/backup')));

        file_put_contents($this->getRealOutDir() . '/ci-history.json', json_encode($this->history));
        $this->history = []; // reset history before ci-config
        file_put_contents($this->getRealOutDir() . '/ci-config.json', json_encode(get_object_vars($this)));
        // $proc = Proc::run(new Command('tar', '--append', '--file=' . $dir . '/volume.tar', $dir . '/ci-history.json'));
        // $proc = Proc::run(new Command('zip', '-r', $dir . '/volume.zip', $dir . '/ci-history.json'));
        // var_dump($proc->stderr);
    }

    public function volumeInspect(): string
    {
        $proc = $this->runCommand(new NamedCommand('Inspect volume', 'docker', 'inspect', $this->getVolume()->name));
        return $proc->stdout;
    }

    public function removeVolume()
    {
        $this->runCommand(new NamedCommand('Remove volume', 'docker', 'volume', 'rm', $this->volume->name));
    }

    public function getTagName(): string
    {
        return 'tag_php' . $this->phpVersion;
    }

    /**
     * @return string
     */
    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }

    /**
     * @param string $phpVersion
     * @return CIMachine
     */
    public function setPhpVersion(string $phpVersion): CIMachine
    {
        $this->phpVersion = $phpVersion == 'latest' ? self::PHP_LATEST : $phpVersion;
        return $this;
    }

    /**
     * @return Mount
     */
    public function getVolume(): Mount
    {
        return $this->volume;
    }

    /**
     * @return GitLocation
     */
    public function getLocation(): GitLocation
    {
        return $this->location;
    }

    /**
     * @return array
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * @param string $directory
     * @return CIMachine
     */
    public function setDirectory(string $directory): CIMachine
    {
        $this->directory = rtrim($directory, '/');
        return $this;
    }

    /**
     * @return Logger|null
     */
    public function getLogger(): ?Logger
    {
        return $this->logger;
    }

    /**
     * @param Logger|null $logger
     * @return CIMachine
     */
    public function setLogger(?Logger $logger): CIMachine
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return string
     */
    public function getDatabaseType(): string
    {
        return $this->databaseType;
    }

    /**
     * @param string $databaseType
     * @return CIMachine
     */
    public function setDatabaseType(string $databaseType): CIMachine
    {
        $this->databaseType = $databaseType;
        return $this;
    }

    /**
     * @return string
     */
    public function getDatabaseVersion(): string
    {
        return $this->databaseVersion;
    }

    /**
     * @param string $databaseVersion
     * @return CIMachine
     */
    public function setDatabaseVersion(string $databaseVersion): CIMachine
    {
        $this->databaseVersion = $databaseVersion;
        return $this;
    }

    public function setEnvVars(mixed $vars)
    {
        $this->envVars = $vars;
    }
}
