<?php

/*
 * Usage:
 *      php dockertest.php | docker build -
 *    
 * use Tivins\CIMachine\DockerFile;
 * require 'vendor/autoload.php';
 * $dockerFile = new DockerFile();
 * $dockerFile->setPHPVersion('8.1');
 * $dockerFile->setPHPVersion('7.3');
 * $dockerFile->addPHPExtension('xdebug');
 * $dockerFile->addPHPExtension('imagick');
 * $dockerFile->addPHPExtension('intl');
 * $dockerFile->addPHPExtension('zip');
 * $dockerFile->addPHPExtension('json');
 * echo $dockerFile;
 */
namespace Tivins\CIMachine;

enum PHPExtension: string
{
    case XDEBUG = 'xdebug';
    case IMAGICK = 'imagick';
    case MBSTRING = 'mbstring';
    case ZIP = 'zip';
    case GD = 'gd';
    case INTL = 'intl';
    case JSON = 'json';
}

class DockerFile
{
    private string $phpVersion = 'latest';
    private array $phpModulesEnabled = [];
    private bool $databaseEnabled = false;
    private string $databaseServer = 'mysql';
    private string $databaseVersion = 'latest';

    private array $ext = [
        'peclInstall' => [],
        'extConfig' => [],
        'extEnable' => [],
        'extInstall' => [],
        'libs' => [],
    ];

    public function __construct()
    {
    }

    public function setPHPVersion(string $version): static
    {
        $this->phpVersion = $version;
        return $this;
    }

    public function addPHPExtension(PHPExtension $name): static
    {
        switch ($name) {
            case PHPExtension::XDEBUG:
                $this->ext['peclInstall'][] = 'xdebug';
                $this->ext['extEnable'][] = 'xdebug';
                break;
            case PHPExtension::IMAGICK:
                $this->ext['libs'][] = 'libmagickwand-dev';
                $this->ext['peclInstall'][] = 'imagick';
                $this->ext['extEnable'][] = 'imagick';
                break;
            case PHPExtension::MBSTRING:
                $this->ext['extInstall'][] = 'mbstring';
                break;
            case PHPExtension::ZIP:
                $this->ext['libs'][] = 'libzip-dev';
                $this->ext['extInstall'][] = 'zip';
                break;
            case PHPExtension::GD;
                $this->ext['extInstall'][] = 'gd';
                break;
            case PHPExtension::INTL:
                $this->ext['libs'][] = 'libicu-dev';
                $this->ext['extConfig'][] = 'intl';
                $this->ext['extInstall'][] = 'intl';
                break;
        }
        return $this;
    }

    function getFingerPrint(): string
    {
        return sha1(json_encode(get_object_vars($this)));
    }

    public function __toString(): string
    {
        $body = '';
        $body .= '# ' . str_repeat('-', 78) . "\n";
        $body .= "# Dockerfile generated on " . gmdate('c') . "\n";
        $body .= "# Fingerprint : " . $this->getFingerPrint() . "\n";
        $body .= '# ' . str_repeat('-', 78) . "\n";
        $body .= "\n";

        $body .= "FROM php:{$this->phpVersion}-fpm\n\n";

        $body .= "# Update and install\n";
        $body .= "RUN apt-get update" . (!empty($this->ext['libs']) ? ' && apt install -y --no-install-recommends ' . implode(' ', $this->ext['libs']) . "\n" : '') . "\n\n";

        $body .= $this->installPHPDeps();

        $body .= "# Setup xdebug for code coverage\n";
        $body .= "RUN echo 'xdebug.mode=coverage' > /usr/local/etc/php/conf.d/xdebug.ini\n\n";

        $body .= $this->addComposer();

        $body .= "RUN useradd -ms /bin/bash admin && echo \"admin:admin\" | chpasswd" . "\n";
        $body .= "USER admin" . "\n\n";

        $body .= "WORKDIR /box" . "\n\n";
        return $body;
    }

    private function installPHPDeps(): string
    {
        $directives = [];
        if (!empty($this->ext['peclInstall'])) {
            $directives[] = 'pecl install ' . implode(' ', $this->ext['peclInstall']);
        }
        if (!empty($this->ext['extConfig'])) {
            $directives[] = 'docker-php-ext-configure ' . implode(' ', $this->ext['extConfig']);
        }
        if (!empty($this->ext['extEnable'])) {
            $directives[] = 'docker-php-ext-enable ' . implode(' ', $this->ext['extEnable']);
        }
        if (!empty($this->ext['extInstall'])) {
            $directives[] = 'docker-php-ext-install ' . implode(' ', $this->ext['extInstall']);
        }
        if (empty($directives)) {
            return '';
        }
        return "# PHP extensions\n"
            . 'RUN ' . implode(" \\ \n    && ", $directives) . "\n\n";
    }

    private function addComposer(): string
    {
        return "# Composer\n"
            . 'RUN curl -sS https://getcomposer.org/installer | '
            . 'php -- --install-dir=/usr/local/bin --filename=composer' . "\n"
            // . 'RUN composer --version' . "\n"
            . "\n";
    }
}
