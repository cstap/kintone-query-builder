# kintone query builder

[![CircleCI](https://circleci.com/gh/toyokumo/kintone-query-builder-php.svg?style=svg)](https://circleci.com/gh/toyokumo/kintone-query-builder-php)

[![PHP Version](https://img.shields.io/badge/php-7.2-pink.svg?style=flat-square)]()
[![Latest Stable Version](https://poser.pugx.org/toyokumo/kintone-query-builder/v/stable)](https://packagist.org/packages/toyokumo/kintone-query-builder)
[![Latest Unstable Version](https://poser.pugx.org/toyokumo/kintone-query-builder/v/unstable)](https://packagist.org/packages/toyokumo/kintone-query-builder)
[![License](https://poser.pugx.org/toyokumo/kintone-query-builder/license)](https://packagist.org/packages/toyokumo/kintone-query-builder)

[Kintone REST API](https://developer.cybozu.io/hc/ja/articles/202331474)のためのクエリビルダーです。. Kintone query builderは`GET /records.json`の`query`パラメータ用の文字列を組み立てるのを助けます。
## usage
### インストール
```
composer require toyokumo/kintone-query-builder:v1.0.0
composer install
```
### 基本的な例
```php
<?php
use KintoneQueryBuilder\KintoneQueryBuilder;
use KintoneQueryBuilder\KintoneQueryExpr;
// example
// すべての演算子(=, !=, like, not like, <, >, <=, >=, in, not in)が使えます
(new KintoneQueryBuilder())->where('name', '=', 'hoge')->build();
// => 'name = "hoge"'
(new KintoneQueryBuilder())
    ->where('favorite', 'in', ['apple', 'banana', 'orange'])
    ->build();
// => 'favorite in ("apple","banana","orange")'
(new KintoneQueryBuilder())
    ->where('age', '>', 10)
    ->andWhere('name', 'like', 'banana') // かわりにwhereと書くことができます(where = andWhere)
    ->andWhere('name', '!=', 'banana')
    ->build();
// => 'age > 10 and name like "banana" and name != "banana"'
(new KintoneQueryBuilder())
    ->where('age', '>', 20)
    ->orderBy('$id', 'desc')
    ->limit(50)
    ->build();
// => 'age > 20 order by $id desc limit 50'
(new KintoneQueryBuilder()) // ネストしたクエリには、KintoneQueryExprを$builder->whereの引数として渡してください。
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
// ダブルクオートはエスケープされます
// => 'x = "ho\"ge"'


```

### 例: kintone APIからの全レコードの取得
kintone APIの制限のため、一度に501レコード以上取得することはできません。以下のようなコードが必要になったときはkintone query builderは便利です。
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

### 注意：メソッドは変更的です
`$builder->where(...)`は、新しい`$builder`のコピーを生成しそれを返す代わりに、`$builder`を変更してそれ自身を返します。この挙動は、以下のような予期しない挙動を引き起こすかもしれません。
```php
<?php
$builder = (new KintoneQueryBuilder());
$q0 = $builder->where('x', '=', 1)->build();
// $q0 = 'x = 1'
$q1 = $builder->where('y', '=', 1)->bulid();
// $q1 = 'x = 1 and y = 1', not 'y = 1'
```
`$builder`に`y = 1`を返してほしい場合、ファクトリ関数（もしくはファクトリクラス）を定義すべきです。
```php
<?php
function getBaseBuilder() {
    return (new KintoneQueryBuilder())->where('x', '=', 1);
}
$q0 = getBaseBuilder()->build();
$q1 = getBaseBuilder()->where('y', '=', 1)->build();
$q2 = getBaseBuilder()->bulid(); // you can get 'x = 1' again
```
このパターンは、同じビルダーを使ってちょっと異なったクエリを生成するのに便利です。

[他の例](https://github.com/cstap/kintone-query-builder/blob/master/tests/QueryTest.php).

## how to contribute
Feel free to send us pull requests.
### setup
```
git clone git@github.com:cstap/kintone-query-builder.git
composer install
```
### testing
Run `composer test`.
### format
Install [prettier](https://prettier.io/) and run `composer format`.
## License
[MIT](https://github.com/cstap/kintone-query-builder/blob/master/LICENSE)
