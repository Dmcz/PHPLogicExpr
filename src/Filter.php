<?php

declare(strict_types=1);

namespace Dmcz\LogicExpr;

use Exception;

/**
 * DESIGN NOTE
 * 1. 选择继承的原因有以下几点
 *  * 嵌套时需要继承allowFields，使得group callable中接受的Filter同样支持字段限制
 *  * 仅在append处调整逻辑即可实现字段限制.
 */

/**
 * Undocumented class.
 */
class Filter extends Condition
{
    /**
     * @var Constraint[]
     */
    protected array $constraints = [];

    /**
     * @var null|string[]
     */
    protected ?array $allowFields = null;

    public function __construct()
    {
        return parent::__construct();
    }

    public function __get($name)
    {
        $this->checkFieldAllowed($name);

        if (! array_key_exists($name, $this->constraints)) {
            $this->constraints[$name] = new Constraint($name);
        }

        return $this->constraints[$name];
    }

    public function __set($name, $value)
    {
        $this->checkFieldAllowed($name);

        if ($value instanceof Constraint) {
            if ($value->name != $name) {
                throw new Exception('Inconsistent names.');
            }

            $constraint = $value;
        } else {
            $constraint = new Constraint($name)->equal($value);
        }

        $this->constraints[$name] = $constraint;
    }

    /**
     * @var Constraint[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function countConstraints(): int
    {
        return count($this->constraints);
    }

    public function append(Expression|ExpressionTree $expression, ?Logic $logic = null): static
    {
        if ($expression instanceof Expression) {
            if ($expression->left instanceof Identifier) {
                $this->checkFieldAllowed($expression->left->name);
            }

            if ($expression->right instanceof Identifier) {
                $this->checkFieldAllowed($expression->right->name);
            }
        }

        return parent::append($expression, $logic);
    }

    public function checkFieldAllowed(string $name): void
    {
        if ($this->allowFields !== null && ! in_array($name, $this->allowFields, true)) {
            throw new Exception("The field '{$name}' not allowed in " . get_class($this), 1);
        }
    }

    public function isEmpty(): bool
    {
        foreach ($this->constraints as $constraint) {
            if (! $constraint->isEmpty()) {
                return false;
            }
        }

        return parent::isEmpty();
    }
}
