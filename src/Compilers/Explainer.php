<?php

declare(strict_types=1);

namespace Dmcz\LogicExpr\Compilers;

use Exception;
use Dmcz\LogicExpr\Filter;
use Dmcz\LogicExpr\Literal;
use Dmcz\LogicExpr\Expression;
use Dmcz\LogicExpr\Identifier;
use Dmcz\LogicExpr\ExpressionTree;
use Dmcz\LogicExpr\Logic;

class Explainer
{
    public function __construct(
    ) {
    }

    public function compile(Expression|ExpressionTree $expression): string
    {
        if ($expression instanceof Expression){
            return $this->compileExpression($expression);
        }else{
            if ($expression instanceof Filter) {
                return $this->compileFilter($expression);
            }else{
                return $this->compileExpressionTree($expression);
            }
        }
    }

    public function compileFilter(Filter $filter): string
    {   

        $constraints = $filter->getConstraints();
        $constraintTotal = $filter->countConstraints();
        $expressionTotal = $filter->countExpressions();

        // DESIGN NOTE: 区分多种情况，主要是避免没有意义的括号

        // 没有约束
        if($constraintTotal == 0 && $expressionTotal > 0){  
            return $this->compileExpressionTree($filter);
        }
        
        // 仅有约束
        if($constraintTotal > 0 && $expressionTotal == 0){ 
            return $this->compileConstraints($constraints);
        }

        // 约束优先与条件
        // 约束和条件表达式之间的关系为且
        $text = $this->compileConstraints($constraints);

        $text .= " and "; 
        
        if($expressionTotal == 1){  // 单条表达式
            $text .= $this->compileExpressionTree($filter);
        }else{ // 多条表达式
            if($filter->getLogic() === Logic::AND){ // 当逻辑是与的是可以省略括号
                $text .= $this->compileExpressionTree($filter);
            }else{
                $text .= "(" . $this->compileExpressionTree($filter) . ")";
            }
        }

        return $text;
    }

    public function compileConstraints(array $constraints): string
    {
        $text = '';

        foreach($constraints as $constraint){
            // 多个约束间的关系为且
            if($text != ''){
                $text .= " and ";
            }

            // 单条约束中存在多个表达式,其最最外层不为and时需要被包裹
            if($constraint->countExpressions() > 1){ 
                if($constraint->getLogic() == Logic::AND){
                    $text .= $this->compileExpressionTree($constraint);
                }else{
                    $text .= '(' . $this->compileExpressionTree($constraint) . ')';
                }
                
            }else{
                $text .= $this->compileExpressionTree($constraint);
            }
        }
        
        return $text;
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

            if($subExpression instanceof Expression){
                $text .= $this->compileExpression($subExpression);
            }else if($subExpression instanceof Filter){
                if($expressionTree->getLogic() == $subExpression->getLogic() || ($expressionTree->getLogic() == null && $subExpression->getLogic() == Logic::AND)){ # 相同逻辑可以省略括号
                    $text .= $this->compileFilter($subExpression);
                }else{
                    $text .= '(' . $this->compileFilter($subExpression) . ')';
                }
                
            }else if($subExpression instanceof ExpressionTree){
                if(($expressionTree->getLogic() == $subExpression->getLogic()) || ($expressionTree->getLogic() == null && $subExpression->getLogic() == Logic::AND)){ # 相同逻辑可以省略括号
                    $text .= $this->compileExpressionTree($subExpression);                    
                }else{
                    $text .= '(' . $this->compileExpressionTree($subExpression) . ')';
                }

            }else{
                throw new \Exception("The express not support");
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
