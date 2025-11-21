# 基本概念
## 表达式
* 左操作符+运算符+右操作符为表达式，例如: `a > 1`
## 条件表达式
* 将多个表达式按照统一的逻辑关系（AND 或 OR）组合在一起，形成一个具有明确优先级和逻辑边界的复合表达式称为条件表达式，例如：`foo = 0 AND bar = 2`
* 条件表达式内通过括号表示优先级，我们将括号内的表达式称为表达式组，例如：`(foo > 0 AND foo < 10) or bar = "a"`
* 表达式组内的所有表达式必须使用 相同的逻辑运算符（全部 AND 或全部 OR），不能混用。例如：`foo < 0 and foo > 10 or bar = "a"` 该表达式中存在歧义，需要用括号来表示优先级。
## 约束
* 作用在同一个标识符上的条件表达式称为约束，例如：`(foo > 0 AND foo < 10) or foo = 15`

# 使用示例
> 更多用法参考单元测试
## 基本语法
### Equal
```php
# foo = "a" 
new Condition()->where("foo", "a");
new Condition()->where("foo", "=", "a");
new Condition()->where("foo", Operator::EQ, "a");
new Condition()->equal("foo", "a");

new Constraint("foo")->equal("a");
```
### Not Equal
```php
# foo != "a"
new Condition()->where("foo", "!=", "a");
new Condition()->where("foo", "<>", "a");
new Condition()->where("foo", Operator::NEQ, "a");
new Condition()->notEqual("foo", "a");

new Constraint("foo")->notEqual("a");
```
### Greater Than
```php

```
## 逻辑语法
### and
```php
# foo > 0 and foo < 10
new Condition()->where("foo", ">", "0")->where("foo", "<", "10");
new Condition()->where("foo", ">", "0")->where("foo", "<", "10", Logic::AND); // 第四个参数默认为AND
new Condition()->greaterThan("foo", "0")->lessThan("foo", "10"); 

new Constraint("foo")->greaterThan("0")->lessThan("10");
```
### or
```php
# foo = "a" or foo = "b"
new Condition()->where("foo", "a")->orWhere("foo", "b");
new Condition()->where("foo", "a")->where("foo", "b", logic: Logic::OR); // 第四个参数默认为AND
new Condition()->equal("foo", "a")->orEqual("foo", "b");

new Constraint("foo")->equal("a")->orEqual("b");
```
### 组合
```php
# (foo < 0 or foo > 10) and bar = "a" and (baz = "b" or qux in (1,2,3))
new Condition()->where(function(Condition $condition){
    $condition->lessThan("foo", 0)->orGreaterThan("foo", 10);
})->where("bar", "a")->where(function(Condition $condition){
    $condition->where("baz", 0)->in("qux", [1,2,3]);
})

# (foo = 0 or foo = 10) or (foo >= 20 and foo <=30)
new Constraint("foo")
->group(fn (Constraint $constraint) => $constraint->equal(0)->orEqual(10))
->or(fn (Constraint $constraint) => $constraint->greateEqual(20)->lessEqual(30))

```