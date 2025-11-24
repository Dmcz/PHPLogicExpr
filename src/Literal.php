<?php

declare(strict_types=1);

namespace Dmcz\LogicExpr;

/**
 * Literal 表示一个字面量值
 * 用于表达式中作为固定值出现，例如字符串、数字、布尔值或其它数据。
 */
class Literal
{
    public function __construct(
        public readonly mixed $value
    ) {
    }
}
