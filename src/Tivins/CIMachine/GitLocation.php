<?php

namespace Tivins\CIMachine;

use InvalidArgumentException;
use JsonSerializable;

class GitLocation implements JsonSerializable
{
    public const BRANCH_DEFAULT = 'default';
    public const COMMIT_DEFAULT = 'HEAD';

    public function __construct(
        public string $uri,
        public string $branch = self::BRANCH_DEFAULT,
        public string $commit = self::COMMIT_DEFAULT,
    )
    {
        if (empty($uri)) {
            throw new InvalidArgumentException('URI cannot be empty');
        }
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}