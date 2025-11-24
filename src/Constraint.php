<?php

declare(strict_types=1);

namespace Dmcz\LogicExpr;

use Dmcz\LogicExpr\Compilers\Explainer;

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

     public function greaterThan(mixed $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::greaterThan($this->name, $value), $logic);
    }

    public function orGreaterThan(mixed $value): static
    {
        return $this->greaterThan($value, Logic::OR);
    }

    public function greaterEqual(mixed $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::greaterEqual($this->name, $value), $logic);
    }

    public function orGreaterEqual(mixed $value): static
    {
        return $this->greaterEqual($value, Logic::OR);
    }

    public function lessThan(mixed $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::lessThan($this->name, $value), $logic);
    }

    public function orLessThan(mixed $value): static
    {
        return $this->lessThan($value, Logic::OR);
    }

    public function lessEqual(mixed $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::lessEqual($this->name, $value), $logic);
    }

    public function orLessEqual(mixed $value): static
    {
        return $this->lessEqual($value, Logic::OR);
    }

    public function in(array $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::in($this->name, $value), $logic);
    }

    public function orIn(array $value): static
    {
        return $this->in($value, Logic::OR);
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
