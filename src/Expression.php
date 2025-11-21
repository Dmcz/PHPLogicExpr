<?php

declare(strict_types=1);

namespace Dmcz\FilterBlocks;

class Expression
{
    public function __construct(
        public readonly Identifier|Literal $left,
        public readonly Operator $operator,
        public readonly Identifier|Literal|null $right,
    ) {
    }
}
