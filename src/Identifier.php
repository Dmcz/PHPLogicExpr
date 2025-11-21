<?php

declare(strict_types=1);

namespace Dmcz\FilterBlocks;

/**
 * Identifier表示一个“标识符”
 * 用于引用某个命名实体，例如字段名、变量名、属性名等。
 */
class Identifier
{
    public function __construct(
        public readonly string $name
    ) {
    }
}
