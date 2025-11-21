<?php

declare(strict_types=1);

namespace Dmcz\FilterBlocks;

use Dmcz\FilterBlocks\Compilers\Explainer;

class Constraint extends ExpressionTree
{
    /**
     * 解释器.
     */
    protected Explainer $explainer;

    public function __construct(
        public readonly string $name,
    ) {
        $this->explainer = new Explainer();
    }

    public function equal(mixed $value, Logic $logic = Logic::AND): static
    {
        $this->append(Expr::equal($this->name, $value), $logic);
        return $this;
    }

    public function orEqual(mixed $value): static
    {
        return $this->equal($value, Logic::OR);
    }

    public function group(callable $callback, Logic $logic = Logic::AND): static
    {
        $constraint = new static($this->name);
        $this->append($constraint, $logic);

        $callback($constraint);
        return $this;
    }

    public function or(callable $callback): static
    {
        return $this->group($callback, Logic::OR);
    }

    public function and(callable $callback): static
    {
        return $this->group($callback, Logic::AND);
    }

    public function explain(): string
    {
        return $this->explainer->compileExpressionTree($this);
    }
}
