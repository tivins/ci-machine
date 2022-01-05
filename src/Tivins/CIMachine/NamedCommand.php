<?php

namespace Tivins\CIMachine;

use Tivins\Core\Proc\Command;

class NamedCommand extends Command
{
    public function __construct(public string $name, string ...$command)
    {
        parent::__construct(...$command);
    }
}