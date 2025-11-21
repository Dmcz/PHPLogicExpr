<?php

declare(strict_types=1);

namespace Dmcz\FilterBlocks\Compilers;

use Dmcz\FilterBlocks\Expression;
use Dmcz\FilterBlocks\ExpressionTree;
use Dmcz\FilterBlocks\Identifier;
use Dmcz\FilterBlocks\Literal;
use Exception;

class Explainer
{
    public function __construct(
    ) {
    }

    public function compile(Expression|ExpressionTree $expression): string
    {
        if ($expression instanceof ExpressionTree) { // 表达式树
            return $this->compileExpressionTree($expression);
        }  // 表达式
        return $this->compileExpression($expression);
    }

    public function compileExpressionTree(ExpressionTree $expressionTree): string
    {
        $text = '';

        foreach ($expressionTree->getExpressions() as $subExpression) {
            if ($text != '') {
                if ($expressionTree->getLogic() === null) {
                    throw new Exception('The ....', 1);
                }

                $text .= ' ' . $expressionTree->getLogic()->value . ' ';
            }

            if ($subExpression instanceof ExpressionTree) {
                $text .= '(' . $this->compile($subExpression) . ')';
            } else {
                $text .= $this->compile($subExpression);
            }
        }

        return $text;
    }

    public function compileExpression(Expression $expression): string
    {
        $left = $this->operandToString($expression->left);
        $right = $this->operandToString($expression->right);

        $text = $left . ' ' . $expression->operator->value;
        if ($right !== '') {
            $text .= ' ' . $right;
        }

        return $text;
    }

    public function operandToString(Identifier|Literal|null $operand): string
    {
        if ($operand instanceof Literal) {
            if (is_null($operand->value)) {
                $value = 'null';
            } elseif (is_float($operand->value) || is_int($operand->value)) { // 数值
                $value = (string) $operand->value;
            } elseif (is_bool($operand->value)) {
                $value = $operand->value ? 'true' : 'false';
            } elseif (is_array($operand->value)) {
                $value = implode(',', $operand->value);
            } else { // string
                $value = '"' . $operand->value . '"';
            }

            return $value;
        }
        if (is_null($operand)) {
            return '';
        }
        return (string) $operand->name;
    }
}
