<?php

declare(strict_types=1);

namespace Dmcz\FilterBlocks;

use InvalidArgumentException;

enum Operator: string
{
    case GT = '>';            // Greater than.
    case GTE = '>=';           // Greater than or equal to.
    case LT = '<';            // Less than.
    case LTE = '<=';           // Less than or equal to.
    case EQ = '=';            // Equal to.
    case NEQ = '!=';           // Not equal to.
    case IN = 'in';            // Included in a specified set.
    case NOT_IN = 'not in';        // Not included in a specified set.
    case IS_NULL = 'is null';       // Is null.
    case NOT_NULL = 'not null';      // Is not null.
    case CONTAIN = 'contain';       // Contains the specified pattern within the value.
    case START_WITH = 'start with';    // Matches values starting with the specified.
    case END_WITH = 'end with';      // Matches values ending with the specified.

    public static function parse(string $input): self
    {
        return match (strtoupper(trim($input))) {
            '>' => self::GT,
            '>=' => self::GTE,
            '<' => self::LT,
            '<=' => self::LTE,
            '=' => self::EQ,
            '!=', '<>' => self::NEQ,
            'IN' => self::IN,
            'NOT IN', 'NOT_IN' => self::NOT_IN,
            'IS NULL', 'IS_NULL', 'NULL' => self::IS_NULL,
            'IS NOT NULL', 'IS_NOT_NULL', 'NOT NULL', 'NOT_NULL' => self::NOT_NULL,
            'CONTAIN', 'CONTAINS' => self::CONTAIN,
            'START WITH', 'START_WITH' => self::START_WITH,
            'END WITH', 'END_WITH' => self::END_WITH,
            default => throw new InvalidArgumentException('The operator not support'),
        };
    }
}
