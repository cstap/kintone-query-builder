# kintone query builder php

[![CircleCI](https://circleci.com/gh/toyokumo/kintone-query-builder-php.svg?style=svg)](https://circleci.com/gh/toyokumo/kintone-query-builder-php)

[![PHP Version](https://img.shields.io/badge/php-7.2-pink.svg?style=flat-square)]()
[![Latest Stable Version](https://poser.pugx.org/toyokumo/kintone-query-builder/v/stable)](https://packagist.org/packages/toyokumo/kintone-query-builder)
[![Latest Unstable Version](https://poser.pugx.org/toyokumo/kintone-query-builder/v/unstable)](https://packagist.org/packages/toyokumo/kintone-query-builder)
[![License](https://poser.pugx.org/toyokumo/kintone-query-builder/license)](https://packagist.org/packages/toyokumo/kintone-query-builder)


[日本語のREADME](https://github.com/cstap/kintone-query-builder/blob/master/README.ja.md)

Query builder for [Kintone REST API](https://developer.kintone.io/hc/en-us/articles/213149287/) in PHP. Kintone query builder helps you to make a parameter string `query` of `GET /records.json`.
## usage
### installation
```
composer require toyokumo/kintone-query-builder:v1.0.0
composer install
```
### basic
```php
<?php
use KintoneQueryBuilder\KintoneQueryBuilder;
use KintoneQueryBuilder\KintoneQueryExpr;
// example
// all operators(=, !=, like, not like, <, >, <=, >=, in, not in) are supported
(new KintoneQueryBuilder())->where('name', '=', 'hoge')->build();
// => 'name = "hoge"'
(new KintoneQueryBuilder())
    ->where('favorite', 'in', ['apple', 'banana', 'orange'])
    ->build();
// => 'favorite in ("apple","banana","orange")'
(new KintoneQueryBuilder())
    ->where('age', '>', 10)
    ->andWhere('name', 'like', 'banana') // you can write 'where' instead here (where = andWhere).
    ->andWhere('name', '!=', 'banana')
    ->build();
// => 'age > 10 and name like "banana" and name != "banana"'
(new KintoneQueryBuilder())
    ->where('age', '>', 20)
    ->orderBy('$id', 'desc')
    ->limit(50)
    ->build();
// => 'age > 20 order by $id desc limit 50'
(new KintoneQueryBuilder()) // for nested query, pass KintoneQueryExpr to $builder->where.
->where(
    (new KintoneQueryExpr())
        ->where('a', '<', 1)
        ->andWhere('b', '<', 1)
)->orWhere(
    (new KintoneQueryExpr())
        ->where('c', '<', 1)
        ->andWhere('d', '<', 1)
)->build();
// => '(a < 1 and b < 1) or (c < 1 and d < 1)'
(new KintoneQueryBuilder())->where('x', '=','ho"ge')->build()
// escape double quote
// => 'x = "ho\"ge"'
```
### example: fetch all records from kintone API
You can't get more than 501 records because of kintone API restriction. In that situation, kintone query builder is very useful.
```php
<?php
use KintoneQueryBuilder\KintoneQueryBuilder;
$builder = (new KintoneQueryBuilder())->where(...);
$records = $api->fetch($builder.build());
$offset = 0;
$records_max = 500; // max records you can get at once (kintone API restriction)
while(!\empty($records)) {
    // do something
    $offset+=$records_max;
    $records = $api->fetch($builder->offset($offset)->build());
}
```
### Precautions: methods are mutable methods
Note that `$builder->where(...)` modifies `$builder` and returns itself instead of returning a new copied builder object.
This may cause unexpected behaivor like this.
```php
<?php
$builder = (new KintoneQueryBuilder());
$q0 = $builder->where('x', '=', 1)->build();
// $q0 = 'x = 1'
$q1 = $builder->where('y', '=', 1)->bulid();
// $q1 = 'x = 1 and y = 1', not 'y = 1'
```
If you want `$builder` to return `'y=1'`, you should define a factory function (or a factory class).
```php
<?php
function getBaseBuilder() {
    return (new KintoneQueryBuilder())->where('x', '=', 1);
}
$q0 = getBaseBuilder()->build();
$q1 = getBaseBuilder()->where('y', '=', 1)->build();
$q2 = getBaseBuilder()->bulid(); // you can get 'x = 1' again
```
This pattern is useful if you want to use the same builder again to build a little bit different query.

[More examples](https://github.com/cstap/kintone-query-builder/blob/master/tests/QueryTest.php).

## how to contribute
Feel free to send us pull requests.
### setup
```
git clone git@github.com:cstap/kintone-query-builder.git
composer install
composer test
```

### format
Install [prettier](https://prettier.io/) and run `composer format`.
## License
[MIT](https://github.com/cstap/kintone-query-builder/blob/master/LICENSE)
