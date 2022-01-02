<?php

namespace Tivins\CIMachine;

use Tivins\Core\Proc\Command;

class Mount
{
    public function __construct(
        public string $name,
        public string $containerPath,
    )
    {
    }

    public function getRunCommand(): Command
    {
        return new Command('--mount', 'source=' . $this->name . ',target=' . $this->containerPath);
    }
}
