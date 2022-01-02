<?php

namespace Tivins\CIMachine;

use Tivins\Core\Proc\Command;
use Tivins\Core\Log\Logger;
use Tivins\Core\Proc\Proc;
use Tivins\Core\System\FileSys;

class CIMachine
{
    public const ROOT_DIR   = '/box';
    public const CLONE_DIR  = self::ROOT_DIR . '/clone';
    public const PHP_LATEST = '8.1';

    public readonly string $uid;
    private string         $phpVersion      = self::PHP_LATEST;
    private Mount          $volume;
    private GitLocation    $location;
    private string         $backupDirectory = '/tmp/cim/[uid]';
    private array          $history         = [];
    private ?Logger        $logger          = null;


    public function __construct(GitLocation $location)
    {
        $this->uid      = 'ci_' . sha1(json_encode([$location, microtime(true)]));
        $this->location = $location;
        $this->volume   = new Mount($this->uid, self::ROOT_DIR);
    }

    public function getRealOutDir(): string
    {
        return str_replace('[uid]', $this->uid, $this->backupDirectory);
    }

    public function run()
    {
        $this->logger?->info("Building image...");
        $this->runCommand($this->dockerBuildCommand());
        $this->runDockerCommand(new Command('php', '-v'));
        $this->runDockerCommand(new Command('composer', '--version'));
        $this->logger?->info('Clone repository...');
        $this->runDockerCommand($this->gitCloneCommand());
        $this->runDockerCommand(new Command('cp', '-r', 'clone', 'repository'));
        $this->runDockerCommand(new Command('git', 'status'), self::CLONE_DIR);
        $this->logger?->info('Install composer...');
        $this->runDockerCommand(new Command('composer', 'install', '--no-interaction'), self::CLONE_DIR);
        $this->logger?->info('Run tests...');
        $this->runDockerCommand($this->getPHPUnitCommand(), self::CLONE_DIR);
    }

    public function close()
    {
        $this->logger?->info('Closing...');
        $this->removeVolume();
    }

    public function doRunBackupClose()
    {
        $this->run();
        $this->logger?->info('Backup...');
        $this->backup();
        $this->close();
    }

    private function runCommand(Command $command): Proc
    {
        $this->logger->debug(__function__, $command->get());
        return $this->history[] = Proc::run($command);
    }

    private function runDockerCommand(Command $command, ?string $workDir = null): void
    {
        $this->runCommand($this->dockerRunCommand($command, $workDir));
    }

    /**
     * Return a "docker build" command.
     *
     * Schema
     *      docker build --build-arg [args] -t [tag] -f [Dockerfile] .
     */
    public function dockerBuildCommand(): Command
    {
        $command = new Command('docker', 'build');
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
     * @param Command $runCommand The command to run inside the container.
     * @param string|null $workDir The working directory to run the command.
     * @param Command|null $extra Extraneous command for "docker run".
     * @return Command The command to run.
     */
    public function dockerRunCommand(Command $runCommand, ?string $workDir = null, ?Command $extra = null): Command
    {
        $tag     = $this->getTagName();
        $command = new Command('docker', 'run');
        $command->add('--rm');
        $command->add('--name', "run$tag");
        if ($workDir) {
            $command->add('--workdir', $workDir);
        }
        $command->addCommand($extra);
        $command->addCommand($this->volume->getRunCommand());
        $command->add($tag);
        $command->addCommand($runCommand);
        return $command;
    }

    /**
     * Get the "git clone" command.
     *
     * @param int $depth
     * @return Command
     */
    public function gitCloneCommand(int $depth = 50): Command
    {
        $cmd = new Command('git', 'clone');
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
     * @return Command
     */
    public function getPHPUnitCommand(): Command
    {
        return (new Command('vendor/bin/phpunit'))
            ->add('--log-teamcity', '/box/phpunit-logs')
            ->add('--coverage-clover', '/box/clover.xml')
            ->add('--teamcity');
    }

    public function volumeInspect(): string
    {
        $proc = $this->runCommand(new Command('docker', 'inspect', $this->getVolume()->name));
        return $proc->stdout;
    }

    private function backup()
    {
        $this->runDockerCommand(new Command('rm', '-rf', self::CLONE_DIR));
        $dir = $this->getRealOutDir();

        FileSys::mkdir($dir);
        $tarCommand = new Command('zip', '-r', '/backup/volume.zip', '/box/');
        $this->runCommand($this->dockerRunCommand($tarCommand, extra: new Command('-v', $dir . ':/backup')));
        file_put_contents($dir . '/ci-history.json', json_encode($this->history));
        $this->history = [];
        file_put_contents($dir . '/ci-config.json', json_encode(get_object_vars($this)));
        // $proc = Proc::run(new Command('tar', '--append', '--file=' . $dir . '/volume.tar', $dir . '/ci-history.json'));
        // $proc = Proc::run(new Command('zip', '-r', $dir . '/volume.zip', $dir . '/ci-history.json'));
        // var_dump($proc->stderr);
    }

    public function removeVolume()
    {
        $this->runCommand(new Command('docker', 'volume', 'rm', $this->volume->name));
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
    public function getBackupDirectory(): string
    {
        return $this->backupDirectory;
    }

    /**
     * @param string $backupDirectory
     * @return CIMachine
     */
    public function setBackupDirectory(string $backupDirectory): CIMachine
    {
        $this->backupDirectory = rtrim($backupDirectory, '/');
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
}
