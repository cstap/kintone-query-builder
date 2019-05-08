<?php

namespace KintoneQueryBuilder;

/**
 * Class KintoneQueryExpr
 *
 * This class builds logical condition clauses.
 * Note that you can't specify 'offset' or 'order by' with this class.
 * In that case, you should use KintoneQueryBuilder.
 * KintoneQueryExpr can be a argument of new KintoneQueryBuilder() to build  a nested query like '(A and B) or (C and D)'.
 *
 * @package KintoneQueryBuilder
 *
 */

class KintoneQueryExpr
{
    /**
     * @var KintoneQueryBuffer $buffer
     */
    protected $buffer;

    /**
     * KintoneQueryExpr constructor.
     */
    public function __construct()
    {
        $this->buffer = new KintoneQueryBuffer();
    }

    /**
     * @param string $s
     * @return bool
     */
    private static function funcCheck(string $s): bool
    {
        // https://developer.cybozu.io/hc/ja/articles/202331474-%E3%83%AC%E3%82%B3%E3%83%BC%E3%83%89%E3%81%AE%E5%8F%96%E5%BE%97-GET-
        // "関数"
        $regexs = [
            '/LOGINUSER\(\)/', // LOGINUSER()
            '/PRIMARY_ORGANIZATION\(\)/',
            '/NOW\(\)/',
            '/TODAY\(\)/',
            '/FROM_TODAY\(\d+,DAYS\)/',
            '/FROM_TODAY\(\d+,WEEKS\)/',
            '/FROM_TODAY\(\d+,MONTHS\)/',
            '/FROM_TODAY\(\d+,YEARS\)/',
            '/THIS_WEEK\(\)/',
            '/THIS_WEEK\(SUNDAY\)/',
            '/THIS_WEEK\(MONDAY\)/',
            '/THIS_WEEK\(TUESDAY\)/',
            '/THIS_WEEK\(WEDNESDAY\)/',
            '/THIS_WEEK\(THURSDAY\)/',
            '/THIS_WEEK\(FRIDAY\)/',
            '/THIS_WEEK\(SATURDAY\)/',
            '/LAST_WEEK\(\)/',
            '/LAST_WEEK\(SUNDAY\)/',
            '/LAST_WEEK\(MONDAY\)/',
            '/LAST_WEEK\(TUESDAY\)/',
            '/LAST_WEEK\(WEDNESDAY\)/',
            '/LAST_WEEK\(THURSDAY\)/',
            '/LAST_WEEK\(FRIDAY\)/',
            '/LAST_WEEK\(SATURDAY\)/',
            '/THIS_MONTH\(\)/',
            '/THIS_MONTH\(([1-9]|([1-2][0-9])|(3[0-1]))\)/',
            '/THIS_MONTH\(LAST\)/',
            '/LAST_MONTH\(\)/',
            '/LAST_MONTH\(([1-9]|([1-2][0-9])|(3[0-1]))\)/',
            '/LAST_MONTH\(LAST\)/',
            '/THIS_YEAR\(\)/'
        ];
        foreach ($regexs as $r) {
            if (preg_match($r, $s)) {
                return true;
            }
        }
        return false;
    }

    /**
     * escape double quote ho"ge -> ho\"ge
     * @param string $s
     * @return string
     */
    private static function escapeDoubleQuote(string $s): string
    {
        return str_replace('"', '\"', $s);
    }

    /**
     * @param string|int|(string|int)[] $val
     * @return string
     * @throws KintoneQueryException
     */
    private static function valToString($val): string
    {
        if (is_string($val)) {
            // you can use function in query
            if (self::funcCheck($val)) {
                return $val;
            }
            return '"' . self::escapeDoubleQuote($val) . '"';
        }
        if (is_int($val)) {
            return (string)$val;
        }
        if (is_array($val)) {
            $list = [];
            foreach ($val as $e) {
                $list[] = self::valToString($e);
            }
            return '(' . implode(',', $list) . ')';
        }
        throw new KintoneQueryException(
            'Invalid $val type: $val must have a string or int or array(used with \'in\' or \'not in\') type, but given ' .
                (is_object($val) ? get_class($val) : (string) $val)
        );
    }

    /**
     * @param string $var
     * @param string $op
     * @param int|string|(int|string)[] $val
     * @return string
     * @throws KintoneQueryException
     */
    public static function genWhereClause($var, $op, $val): string
    {
        // case $op = 'in' or 'not in'
        if ($op === 'in' || $op === 'not in') {
            // expects $val's type to be array
            if (!\is_array($val)) {
                throw new KintoneQueryException(
                    'Invalid $val type: In case $op === \'in\', $val must be array, but given ' .
                        (is_object($val) ? get_class($val) : (string) $val)
                );
            }
        }
        return $var . ' ' . $op . ' ' . self::valToString($val);
    }

    /**
     * @param string $var
     * @param string $op
     * @param int|string|(int|string)[] $val
     * @param string $conj
     * @return self
     * @throws KintoneQueryException
     */
    private function whereWithVarOpVal(
        string $var,
        string $op,
        $val,
        string $conj
    ): self {
        $this->buffer->append(
            new KintoneQueryBufferElement(
                self::genWhereClause($var, $op, $val),
                $conj
            )
        );
        return $this;
    }

    /**
     * @param KintoneQueryExpr $expr
     * @param string $conj
     * @return self
     * @throws KintoneQueryException
     */
    private function whereWithExpr(KintoneQueryExpr $expr, string $conj): self
    {
        if ($expr->buffer->isEmpty()) {
            return $this;
        }
        $expr->buffer->conj = $conj;
        $this->buffer->append($expr->buffer);
        return $this;
    }

    /**
     * @param string|KintoneQueryExpr $varOrExpr
     * @param string $op
     * @param int|string|(int|string)[] $val
     * @return self
     * @throws KintoneQueryException
     */
    public function where($varOrExpr, string $op = '', $val = null): self
    {
        return $this->andWhere($varOrExpr, $op, $val);
    }

    /**
     * @param string|KintoneQueryExpr $varOrExpr
     * @param string $op
     * @param int|string|(int|string)[] $val
     * @return self
     * @throws KintoneQueryException
     */
    public function andWhere($varOrExpr, string $op = '', $val = null): self
    {
        if ($varOrExpr instanceof self) {
            return $this->whereWithExpr($varOrExpr, 'and');
        }
        if (\is_string($varOrExpr)) {
            return $this->whereWithVarOpVal($varOrExpr, $op, $val, 'and');
        }
        throw new KintoneQueryException(
            'Invalid $varOrExpr: $varOrExpr must be string or KintoneQueryExpr, but given ' .
                (is_object($varOrExpr)
                    ? get_class($varOrExpr)
                    : (string) $varOrExpr)
        );
    }

    /**
     * @param string|KintoneQueryExpr $varOrExpr
     * @param string $op
     * @param int|string|(int|string)[] $val
     * @return self
     * @throws KintoneQueryException
     */
    public function orWhere($varOrExpr, string $op = '', $val = null): self
    {
        if ($varOrExpr instanceof self) {
            return $this->whereWithExpr($varOrExpr, 'or');
        }
        if (\is_string($varOrExpr)) {
            return $this->whereWithVarOpVal($varOrExpr, $op, $val, 'or');
        }
        throw new KintoneQueryException(
            'Invalid $varOrExpr: $varOrExpr must be string or KintoneQueryExpr, but given ' .
                (is_object($varOrExpr)
                    ? get_class($varOrExpr)
                    : (string) $varOrExpr)
        );
    }
}
