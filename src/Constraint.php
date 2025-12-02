<?php

declare(strict_types=1);

namespace Dmcz\LogicExpr;

use Dmcz\LogicExpr\Compilers\Explainer;

/**
 * @template T
 */
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

    /**
     * @param T $value
     */
    public function equal($value, Logic $logic = Logic::AND): static
    {
        $this->append(Expr::equal($this->name, $value), $logic);
        return $this;
    }

    /**
     * @param T $value
     */
    public function orEqual($value): static
    {
        return $this->equal($value, Logic::OR);
    }

    /**
     * @param T $value
     */
    public function greaterThan($value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::greaterThan($this->name, $value), $logic);
    }

    /**
     * @param T $value
     */
    public function orGreaterThan($value): static
    {
        return $this->greaterThan($value, Logic::OR);
    }

    /**
     * @param T $value
     */
    public function greaterEqual($value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::greaterEqual($this->name, $value), $logic);
    }

    /**
     * @param T $value
     */
    public function orGreaterEqual($value): static
    {
        return $this->greaterEqual($value, Logic::OR);
    }

    /**
     * @param T $value
     */
    public function lessThan($value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::lessThan($this->name, $value), $logic);
    }

    /**
     * @param T $value
     */
    public function orLessThan($value): static
    {
        return $this->lessThan($value, Logic::OR);
    }

    /**
     * @param T $value
     */
    public function lessEqual($value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::lessEqual($this->name, $value), $logic);
    }

    /**
     * @param T $value
     */
    public function orLessEqual($value): static
    {
        return $this->lessEqual($value, Logic::OR);
    }

    /**
     * @param T[] $value
     */
    public function in(array $value, Logic $logic = Logic::AND): static
    {
        return $this->append(Expr::in($this->name, $value), $logic);
    }

    /**
     * @param T[] $value
     */
    public function orIn(array $value): static
    {
        return $this->in($value, Logic::OR);
    }

    /**
     * @param callable(static):void $callback
     */
    public function group(callable $callback, Logic $logic = Logic::AND): static
    {
        $constraint = new static($this->name);
        $this->append($constraint, $logic);

        $callback($constraint);
        return $this;
    }

    /**
     * @param callable(static):void $callback
     */
    public function or(callable $callback): static
    {
        return $this->group($callback, Logic::OR);
    }

    /**
     * @param callable(static):void $callback
     */
    public function and(callable $callback): static
    {
        return $this->group($callback, Logic::AND);
    }

    public function explain(): string
    {
        return $this->explainer->compileExpressionTree($this);
    }
}
