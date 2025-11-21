<?php

declare(strict_types=1);

namespace Dmcz\FilterBlocks;

// 表达式工厂
class Expr
{
    public static function equal(string $name, mixed $value): Expression
    {
        return new Expression(
            new Identifier($name),
            Operator::EQ,
            self::ensureRight($value),
        );
    }

    public static function notEqual(string $name, mixed $value): Expression
    {
        return new Expression(
            new Identifier($name),
            Operator::NEQ,
            self::ensureRight($value),
        );
    }

    public static function greaterThan(string $name, mixed $value): Expression
    {
        return new Expression(
            new Identifier($name),
            Operator::GT,
            self::ensureRight($value),
        );
    }

    public static function greaterEqual(string $name, mixed $value): Expression
    {
        return new Expression(
            new Identifier($name),
            Operator::GTE,
            self::ensureRight($value),
        );
    }

    public static function lessThan(string $name, mixed $value): Expression
    {
        return new Expression(
            new Identifier($name),
            Operator::LT,
            self::ensureRight($value),
        );
    }

    public static function lessEqual(string $name, mixed $value): Expression
    {
        return new Expression(
            new Identifier($name),
            Operator::LTE,
            self::ensureRight($value),
        );
    }

    public static function in(string $name, array $value): Expression
    {
        return new Expression(
            new Identifier($name),
            Operator::IN,
            self::ensureRight($value),
        );
    }

    public static function notIn(string $name, array $value): Expression
    {
        return new Expression(
            new Identifier($name),
            Operator::NOT_IN,
            self::ensureRight($value),
        );
    }

    public static function isNull(string $name): Expression
    {
        return new Expression(new Identifier($name), Operator::IS_NULL, null);
    }

    public static function notNull(string $name): Expression
    {
        return new Expression(new Identifier($name), Operator::NOT_NULL, null);
    }

    public static function contain(string $name, string $value): Expression
    {
        return new Expression(
            new Identifier($name),
            Operator::CONTAIN,
            self::ensureRight($value),
        );
    }

    public static function startWith(string $name, string $value): Expression
    {
        return new Expression(
            new Identifier($name),
            Operator::START_WITH,
            self::ensureRight($value),
        );
    }

    public static function endWith(string $name, string $value): Expression
    {
        return new Expression(
            new Identifier($name),
            Operator::END_WITH,
            self::ensureRight($value),
        );
    }

    public static function make(string $name, Operator|string $operator, mixed $value): Expression
    {
        $left = new Identifier($name);

        if (! $operator instanceof Operator) {
            $operator = Operator::parse($operator);
        }

        if (in_array($operator, [Operator::IS_NULL, Operator::NOT_NULL])) {
            $right = null;
        } else {
            $right = self::ensureRight($value);
        }

        return new Expression($left, $operator, $right);
    }

    public static function ensureRight(mixed $value): Identifier|Literal
    {
        if (! $value instanceof Identifier && ! $value instanceof Literal) {
            $value = new Literal($value);
        }
        return $value;
    }
}
