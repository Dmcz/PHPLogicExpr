<?php

declare(strict_types=1);

namespace Dmcz\LogicExpr;

use LogicException;

/**
 * 确保所有条件的逻辑运算符（AND/OR）一致。
 *
 * 规则：
 *  - 当条件数量少于 2 个时，不存在逻辑冲突，直接通过。
 *  - 第二个条件的逻辑运算符将作为“全局逻辑基准”。
 *  - 从第三个条件开始，逻辑运算符必须与基准一致，否则视为逻辑混乱。
 *
 * 示例：
 *  ✔ foo = a AND baz = c
 *  ✔ foo = a OR baz = c
 *  ✔ foo = a AND bar = b AND baz = c
 *  ✔ foo = a OR  bar = b OR  baz = c
 *  ✘ foo = a AND bar = b OR  baz = c
 *  ✘ foo = a OR  bar = b AND baz = c
 */
class ExpressionTree
{
    /**
     * @var Expression[]|ExpressionTree
     */
    protected array $expressions = [];

    /**
     * 当前组统一使用的逻辑运算符（AND / OR）.
     */
    protected ?Logic $logic = null;

    public function __construct()
    {
    }

    /**
     * @return Expression[]|ExpressionTree
     */
    public function getExpressions(): array
    {
        return $this->expressions;
    }

    public function countExpressions(): int
    {
        return count($this->expressions);
    }

    public function getLogic(): ?Logic
    {
        return $this->logic;
    }

    public function append(Expression|ExpressionTree $expression, ?Logic $logic = null): static
    {
        if (empty($this->expressions)) { // 第一条表达式, 第一条表达式可以指定或不指定当前的逻辑
            // do nothing..
        } else {// 不是第一条
            // 必须显式传逻辑
            if ($logic === null) {
                throw new LogicException('Logic is required for subsequent expressions in the group.');
            }

            // 如果本组还没确定逻辑，用这次的作为组逻辑
            if ($this->logic === null) {
                $this->logic = $logic;
            } elseif ($this->logic != $logic) { // 注意用
                throw new LogicException('Invalid condition logic: mixed AND/OR operators detected in the same group.');
            }
        }

        $this->expressions[] = $expression;
        return $this;
    }
}
