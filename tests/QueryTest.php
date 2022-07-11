<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use KintoneQueryBuilder\KintoneQueryBuilder;
use KintoneQueryBuilder\KintoneQueryException;
use KintoneQueryBuilder\KintoneQueryExpr;

class QueryTest extends TestCase
{
    public function testWhere()
    {
        $builder = new KintoneQueryBuilder();
        $query = $builder->where('name', '=', 'hoge')->build();
        $this->assertEquals($query, 'name = "hoge"');
    }

    public function testOrderBy(): void
    {
        $builder = new KintoneQueryBuilder();
        $query = $builder->orderBy('id', 'asc')->build();
        $this->assertEquals($query, 'order by id asc');
    }

    public function testLimit(): void
    {
        $builder = new KintoneQueryBuilder();
        $query = $builder->limit(10)->build();
        $this->assertEquals($query, 'limit 10');
    }

    public function testOffset(): void
    {
        $builder = new KintoneQueryBuilder();
        $query = $builder->offset(30)->build();
        $this->assertEquals($query, 'offset 30');
    }

    /**
     * @throws \KintoneQueryBuilder\KintoneQueryException
     */
    public function testMethodChainOrder(): void
    {
        $q0 = (new KintoneQueryBuilder())
            ->orderBy('$id', 'desc')
            ->limit(50)
            ->where('age', '>', '20')
            ->build();
        $q1 = (new KintoneQueryBuilder())
            ->where('age', '>', '20')
            ->orderBy('$id', 'desc')
            ->limit(50)
            ->build();
        $this->assertEquals($q0, $q1);
    }

    /**
     * @throws \KintoneQueryBuilder\KintoneQueryException
     */
    public function testLike(): void
    {
        $q = (new KintoneQueryBuilder())->where('name', 'like', 'hog')->build();
        $this->assertEquals($q, 'name like "hog"');
    }

    /**
     * @throws \KintoneQueryBuilder\KintoneQueryException
     */
    public function testIn(): void
    {
        $q0 = (new KintoneQueryBuilder())
            ->where('favorite', 'in', ['apple', 'banana', 'orange'])
            ->build();
        $this->assertEquals($q0, 'favorite in ("apple","banana","orange")');
        $q1 = (new KintoneQueryBuilder())
            ->where('num', 'in', [1, 2, 4])
            ->build();
        $this->assertEquals($q1, 'num in (1,2,4)');
        $q2 = (new KintoneQueryBuilder())
            ->where('favorite', 'not in', ['kiwi', 'cherry'])
            ->build();
        $this->assertEquals($q2, 'favorite not in ("kiwi","cherry")');
    }

    /**
     * @throws \KintoneQueryBuilder\KintoneQueryException
     */
    public function testAndWhere(): void
    {
        $q = (new KintoneQueryBuilder())
            ->where('age', '>', 10)
            ->andWhere('name', 'like', 'banana')
            ->andWhere('name', '!=', 'banana')
            ->build();
        $this->assertEquals(
            'age > 10 and name like "banana" and name != "banana"',
            $q
        );
        $q = (new KintoneQueryBuilder())->andWhere('x', '>', 10)->build();
        $this->assertEquals('x > 10', $q);
    }

    /**
     * @throws \KintoneQueryBuilder\KintoneQueryException
     */
    public function testOrWhere(): void
    {
        $q0 = (new KintoneQueryBuilder())
            ->where('age', '=', 20)
            ->orWhere('name', '=', 'bob')
            ->build();
        $this->assertEquals($q0, 'age = 20 or name = "bob"');
    }

    public function testOrderByChain(): void
    {
        $q = (new KintoneQueryBuilder())
            ->orderBy('$id', 'desc')
            ->OrderBy('name', 'asc')
            ->OrderBy('age', 'desc')
            ->build();
        $this->assertEquals($q, 'order by $id desc,name asc,age desc');
    }

    /**
     * @throws \KintoneQueryBuilder\KintoneQueryException
     */
    public function testFunctionQuery(): void
    {
        // we can use NOW(), TODAY() etc.
        $tests = [
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'user',
                    '=',
                    'LOGINUSER()'
                ),
                'expected' => 'user = LOGINUSER()'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'org',
                    '=',
                    'PRIMARY_ORGANIZATION()'
                ),
                'expected' => 'org = PRIMARY_ORGANIZATION()'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'NOW()'
                ),
                'expected' => 'time = NOW()'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'TODAY()'
                ),
                'expected' => 'time = TODAY()'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'YESTERDAY()'
                ),
                'expected' => 'time = YESTERDAY()'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'TOMORROW()'
                ),
                'expected' => 'time = TOMORROW()'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '<',
                    'FROM_TODAY(5,DAYS)'
                ),
                'expected' => 'time < FROM_TODAY(5,DAYS)'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '<',
                    'FROM_TODAY(5, DAYS)'
                ),
                'expected' => 'time < FROM_TODAY(5, DAYS)' // with a space
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '<',
                    'FROM_TODAY(5,   DAYS)' // with spaces
                ),
                'expected' => 'time < FROM_TODAY(5,   DAYS)'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '<',
                    'FROM_TODAY(-10,DAYS)'
                ),
                'expected' => 'time < FROM_TODAY(-10,DAYS)'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '<',
                    'FROM_TODAY(-10, DAYS)' // with a space
                ),
                'expected' => 'time < FROM_TODAY(-10, DAYS)'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '<',
                    'FROM_TODAY(-10,   DAYS)' // with spaces
                ),
                'expected' => 'time < FROM_TODAY(-10,   DAYS)'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'THIS_WEEK(SUNDAY)'
                ),
                'expected' => 'time = THIS_WEEK(SUNDAY)'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'THIS_WEEK()'
                ),
                'expected' => 'time = THIS_WEEK()'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'LAST_WEEK(SUNDAY)'
                ),
                'expected' => 'time = LAST_WEEK(SUNDAY)'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'LAST_WEEK()'
                ),
                'expected' => 'time = LAST_WEEK()'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'NEXT_WEEK(SATURDAY)'
                ),
                'expected' => 'time = NEXT_WEEK(SATURDAY)'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'NEXT_WEEK()'
                ),
                'expected' => 'time = NEXT_WEEK()'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'THIS_MONTH()'
                ),
                'expected' => 'time = THIS_MONTH()'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'THIS_MONTH(LAST)'
                ),
                'expected' => 'time = THIS_MONTH(LAST)'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'THIS_MONTH(1)'
                ),
                'expected' => 'time = THIS_MONTH(1)'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'THIS_MONTH(81)'
                ),
                'expected' => 'time = "THIS_MONTH(81)"'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'LAST_MONTH()'
                ),
                'expected' => 'time = LAST_MONTH()'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'LAST_MONTH(LAST)'
                ),
                'expected' => 'time = LAST_MONTH(LAST)'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'LAST_MONTH(1)'
                ),
                'expected' => 'time = LAST_MONTH(1)'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'LAST_MONTH(81)'
                ),
                'expected' => 'time = "LAST_MONTH(81)"'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'NEXT_MONTH()'
                ),
                'expected' => 'time = NEXT_MONTH()'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'NEXT_MONTH(LAST)'
                ),
                'expected' => 'time = NEXT_MONTH(LAST)'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'NEXT_MONTH(1)'
                ),
                'expected' => 'time = NEXT_MONTH(1)'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'NEXT_MONTH(81)'
                ),
                'expected' => 'time = "NEXT_MONTH(81)"'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'THIS_YEAR()'
                ),
                'expected' => 'time = THIS_YEAR()'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'LAST_YEAR()'
                ),
                'expected' => 'time = LAST_YEAR()'
            ],
            [
                'builder' => (new KintoneQueryBuilder())->where(
                    'time',
                    '=',
                    'NEXT_YEAR()'
                ),
                'expected' => 'time = NEXT_YEAR()'
            ]
        ];
        foreach ($tests as $t) {
            $builder = $t['builder'];
            $expected = $t['expected'];
            $this->assertEquals($expected, $builder->build());
        }
    }

    /**
     * @throws \KintoneQueryBuilder\KintoneQueryException
     */
    public function testComplicatedWhere(): void
    {
        $q = (new KintoneQueryBuilder())
            ->where('x', '<', 1)
            ->where('y', '<', 2)
            ->where('z', '<', 1)
            ->build();
        $this->assertEquals('x < 1 and y < 2 and z < 1', $q);
        // A and (B or C)
        $q = (new KintoneQueryBuilder())
            ->where('huga', '<', 1)
            ->where(
                (new KintoneQueryExpr())
                    ->where('piga', '<', 1)
                    ->orWhere('fuga', '<', 1)
            )
            ->build();
        $this->assertEquals('huga < 1 and (piga < 1 or fuga < 1)', $q);
        // (A and B) or (C and D)
        $q = (new KintoneQueryBuilder())
            ->where(
                (new KintoneQueryExpr())
                    ->where('a', '<', 1)
                    ->andWhere('b', '<', 1)
            )
            ->orWhere(
                (new KintoneQueryExpr())
                    ->where('c', '<', 1)
                    ->andWhere('d', '<', 1)
            )
            ->build();
        $this->assertEquals('(a < 1 and b < 1) or (c < 1 and d < 1)', $q);
        // (((A and B) or C) and D)
        $q = (new KintoneQueryBuilder())
            ->where(
                (new KintoneQueryExpr())
                    ->where(
                        (new KintoneQueryExpr())
                            ->where('a', '=', 1)
                            ->andWhere('b', '=', 1)
                    )
                    ->orWhere('c', '=', 1)
            )
            ->andWhere('d', '=', 1)
            ->build();
        $this->assertEquals('((a = 1 and b = 1) or c = 1) and d = 1', $q);
        $q = (new KintoneQueryBuilder())->build();
        $this->assertEquals('', $q);
        $q = (new KintoneQueryBuilder())
            ->where(new KintoneQueryExpr())
            ->where(new KintoneQueryExpr())
            ->where(new KintoneQueryExpr())
            ->build();
        $this->assertEquals('', $q);
        $q = (new KintoneQueryBuilder())
            ->where((new KintoneQueryExpr())->where('x', '<', 1))
            ->build();
        $this->assertEquals('(x < 1)', $q);
        $q = (new KintoneQueryBuilder())
            ->where(new KintoneQueryExpr())
            ->build();
        $this->assertEquals('', $q);
        $q = (new KintoneQueryBuilder())
            ->where(
                (new KintoneQueryExpr())
                    ->where('foo', '=', 20)
                    ->orWhere('bar', '=', 20)
            )
            ->where(
                (new KintoneQueryExpr())
                    ->where('pog', '=', 30)
                    ->orWhere('puga', '=', 30)
            )
            ->build();
        $this->assertEquals(
            '(foo = 20 or bar = 20) and (pog = 30 or puga = 30)',
            $q
        );
        $q = (new KintoneQueryBuilder())
            ->where(
                (new KintoneQueryExpr())
                    ->where(
                        (new KintoneQueryExpr())
                            ->where(
                                (new KintoneQueryExpr())
                                    ->where('a', '<', 10)
                                    ->where('x', '<', 100)
                            )
                            ->where('b', '<', 30)
                    )
                    ->where('c', '<', 20)
            )
            ->where('d', '<', 10)
            ->build();
        $this->assertEquals(
            '(((a < 10 and x < 100) and b < 30) and c < 20) and d < 10',
            $q
        );
        $q = (new KintoneQueryBuilder())
            ->where(new KintoneQueryExpr())
            ->where((new KintoneQueryExpr())->where('x', '<', 10))
            ->build();
        $this->assertEquals('(x < 10)', $q);
        $q = (new KintoneQueryBuilder())
            ->where(new KintoneQueryExpr())
            ->andWhere((new KintoneQueryExpr())->where('x', '<', 10))
            ->build();
        $this->assertEquals('(x < 10)', $q);
    }

    public function testFieldCheck(): void
    {
        $disallowed = 'レコード番号 = "1") or レコード番号 != "0" or (レコード番号';
        // fieldCodeに許可されていない値が含まれている場合
        try {
            $q = (new KintoneQueryBuilder())
                ->orWhere($disallowed, '=', 1)
                ->build();
            $this->fail('不適切なクエリに対して例外が投げられなかった: ' . $q);
        } catch (KintoneQueryException $e) {
            $this->assertIsString($e->getMessage());
        }
        // exprインスタンスのfieldCodeに不正な値を含む場合
        try {
            $expr = (new KintoneQueryExpr())
                ->where($disallowed, '=', 1)
                ->andWhere($disallowed, '<', 1);
            $q = (new KintoneQueryBuilder())
                ->where($expr)
                ->build();
            $this->fail('不適切なクエリに対して例外が投げられなかった: ' . $q);
        } catch (KintoneQueryException $e) {
            $this->assertIsString($e->getMessage());
        }
    }

    public function testSignCheck(): void
    {
        $disallowed = ') or レコード番号 != 0 or (';
        // signに許可された値以外を含む場合
        try {
            $q = (new KintoneQueryBuilder())
                ->orWhere('レコード番号', $disallowed, 1)
                ->build();
            $this->fail('不適切なクエリに対して例外が投げられなかった: ' . $q);
        } catch (KintoneQueryException $e) {
            $this->assertIsString($e->getMessage());
        }
        // exprインスタンスのsignに不正な値を含む場合
        try {
            $expr = (new KintoneQueryExpr())
                ->where('レコード番号', $disallowed, 1)
                ->andWhere('レコード番号2', $disallowed, 1);
            $q = (new KintoneQueryBuilder())
                ->where($expr)
                ->build();
            $this->fail('不適切なクエリに対して例外が投げられなかった: ' . $q);
        } catch (KintoneQueryException $e) {
            $this->assertIsString($e->getMessage());
        }
    }

    public function testEscape(): void
    {
        $q = (new KintoneQueryBuilder())->where('name', '=', 'ho"ge')->build();
        $this->assertEquals('name = "ho\"ge"', $q);
        $q = (new KintoneQueryBuilder())
            ->where('name', 'in', ['ho"ge', 'po"ga', 'piga"""'])
            ->build();
        $this->assertEquals('name in ("ho\"ge","po\"ga","piga\"\"\"")', $q);
    }

    public function testOffsetTwice(): void
    {
        $builder = (new KintoneQueryBuilder())->where('age', '>', 20);
        $q = $builder->offset(10)->build();
        $this->assertEquals('age > 20 offset 10', $q);
        $q = $builder->offset(20)->build();
        $this->assertEquals('age > 20 offset 20', $q);
    }

    public function testLimitTwice(): void
    {
        $builder = (new KintoneQueryBuilder())->where('age', '>', 20);
        $q = $builder->limit(10)->build();
        $this->assertEquals('age > 20 limit 10', $q);
        $q = $builder->limit(20)->build();
        $this->assertEquals('age > 20 limit 20', $q);
    }
}
