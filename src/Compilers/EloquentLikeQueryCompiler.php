<?php

declare(strict_types=1);

namespace Dmcz\LogicExpr\Compilers;

use Exception;
use Dmcz\LogicExpr\Logic;
use Dmcz\LogicExpr\Literal;
use Dmcz\LogicExpr\Operator;
use UnexpectedValueException;
use Dmcz\LogicExpr\Expression;
use Dmcz\LogicExpr\Identifier;
use Dmcz\LogicExpr\ExpressionTree;
use Dmcz\LogicExpr\Filter;

class EloquentLikeQueryCompiler
{
    public function compile(Expression|ExpressionTree $expression, $query)
    {   
        if ($expression instanceof Expression){
            $this->compileExpression($expression, $query, logic: Logic::AND);
        }else{
            if ($expression instanceof Filter) {
                $this->compileFilter($expression, $query);
            }else{
                $this->compileExpressionTree($expression, $query);
            }
        }
    }

    public function compileFilter(Filter $filter, $query): void
    {   
        $constraints = $filter->getConstraints();
        $constraintTotal = $filter->countConstraints();
        $expressionTotal = $filter->countExpressions();

        // DESIGN NOTE：区分多种情况，主要是避免没有意义的括号

        // 没有约束
        if($constraintTotal == 0 && $expressionTotal > 0){  
            $this->compileExpressionTree($filter, $query);
            return;
        }
        
        // 仅有约束
        if($constraintTotal > 0 && $expressionTotal == 0){ 
            $this->compileConstraints($constraints, $query);
            return ;
        }

        // 约束优先与条件
        // 约束和条件表达式之间的关系为且
        $this->compileConstraints($constraints, $query);

        if($expressionTotal == 1){  // 单条表达式
            $this->compileExpressionTree($filter, $query);
        }else{ // 多条表达式
            
            if($filter->getLogic() === Logic::AND){ // 当逻辑是与的是可以省略括号
                $this->compileExpressionTree($filter, $query);
            }else{
                $query->where(function($query) use($filter) {
                    $this->compileExpressionTree($filter, $query);
                });
            }
        }
    }

    public function compileConstraints(array $constraints, $query): void
    {
        foreach($constraints as $constraint){
            // 多个约束间的关系为且
            // 单条约束中存在多个表达式时需要被包裹
            if($constraint->countExpressions() > 1){ 
                if($constraint->getLogic() == Logic::AND){
                    $this->compileExpressionTree($constraint, $query);
                }else{
                    $query->where(function($query) use($constraint) {
                        $this->compileExpressionTree($constraint, $query);
                    });
                }

                
            }else{
                $this->compileExpressionTree($constraint, $query);
            }
        }
    }

    public function compileExpressionTree(ExpressionTree $expressionTree,  $query)
    {
        foreach ($expressionTree->getExpressions() as $subExpression) {
            $logic = $expressionTree->getLogic();
            if($logic === null){
                $logic = Logic::AND;
            }

            if($subExpression instanceof Expression){
                $this->compileExpression($subExpression, $query, $logic);

            }else if($subExpression instanceof Filter){
                if($expressionTree->getLogic() == $subExpression->getLogic() || ($expressionTree->getLogic() == null && $subExpression->getLogic() == Logic::AND)){ # 相同逻辑可以省略括号
                    $this->compileFilter($subExpression, $query, $logic);
                }else{
                    $query->where(function($query) use ($subExpression, $logic){
                        $this->compileExpressionTree($subExpression, $query, $logic);
                    }, boolean: $logic->value);
                }
                
            }else if($subExpression instanceof ExpressionTree){
                if(($expressionTree->getLogic() == $subExpression->getLogic()) || ($expressionTree->getLogic() == null && $subExpression->getLogic() == Logic::AND)){ # 相同逻辑可以省略括号
                    $this->compileExpressionTree($subExpression, $query, $logic);                  
                }else{
                    $query->where(function($query) use ($subExpression, $logic){
                        $this->compileExpressionTree($subExpression, $query, $logic);
                    }, boolean: $logic->value);
                }

            }else{
                throw new \Exception("The express not support");
            }
        }
    }

    public function compileExpression(Expression $expression, $query, Logic $logic)
    {
        $left = $this->ensureOperand($expression->left);
        $right = $this->ensureOperand($expression->right);

        switch ($expression->operator) {
            case Operator::EQ:
                $query->where($left, '=', $right, $logic->value);
                break;
            case Operator::NEQ:
                $query->where($left, '<>', $right, $logic->value);
                break;
            case Operator::GT:
                $query->where($left, '>', $right, $logic->value);
                break;
            case Operator::GTE:
                $query->where($left, '>=', $right, $logic->value);
                break;
            case Operator::LT:
                $query->where($left, '<', $right, $logic->value);
                break;
            case Operator::LTE:
                $query->where($left, '<=', $right, $logic->value);
                break;
            case Operator::IN:
                $query->whereIn($left, $right, $logic->value);
                break;
            case Operator::NOT_IN:
                $query->whereIn($left, $right, $logic->value, true);
                break;
            case Operator::IS_NULL:
                $query->whereNull($left, $logic->value);
                break;
            case Operator::NOT_NULL:
                $query->whereNull($left, $logic->value, true);
                break;
            case Operator::CONTAIN:
                $query->where($left, 'like', '%' . $right . '%',);
                break;
            case Operator::START_WITH:
                $query->where($left, 'like', $right . '%',);
                break;
            case Operator::END_WITH:
                $query->where($left, 'like', '%' . $right);
                break;
            default:
                throw new UnexpectedValueException('The expression not support.');
        };
    }

    public function ensureOperand(Identifier|Literal|null $operand): mixed
    {
        if($operand instanceof Identifier){
            return $operand->name;
        }else if($operand instanceof Literal){
            return $operand->value;
        }else{
            return $operand;
        }
    }
}