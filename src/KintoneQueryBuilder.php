<?php

namespace KintoneQueryBuilder;

/**
 * Class KintoneQueryBuilder
 *
 * This class can do anything KintoneQueryExpr can do.
 * In addition, you can add 'offset' 'limit' 'order by' with this class.
 *
 * @package KintoneQueryBuilder
 */

class KintoneQueryBuilder extends KintoneQueryExpr
{
    /**
     * @var string
     */
    private $orderClause = '';
    /**
     * @var string
     */
    private $limitClause = '';
    /**
     * @var string
     */
    private $offsetClause = '';

    /**
     * @param string $var
     * @param string $ord
     * @return $this
     */
    public function orderBy(string $var, string $ord): self
    {
        if ($this->orderClause === '') {
            $this->orderClause = 'order by ' . $var . ' ' . $ord;
        } else {
            $this->orderClause = $this->orderClause . ',' . $var . ' ' . $ord;
        }
        return $this;
    }

    /**
     * @param int $n
     * @return $this
     */
    public function limit(int $n): self
    {
        $this->limitClause = 'limit ' . $n;
        return $this;
    }

    /**
     * @param int $n
     * @return $this
     */
    public function offset(int $n): self
    {
        $this->offsetClause = 'offset ' . $n;
        return $this;
    }

    /**
     * @return string
     */
    public function build(): string
    {
        $query = '';
        if ($this->buffer !== []) {
            $query = $this->buffer->toQuery();
        }
        $clauses = [
            $this->orderClause,
            $this->limitClause,
            $this->offsetClause
        ];
        foreach ($clauses as $c) {
            if ($c !== '') {
                if ($query !== '') {
                    $query .= ' ' . $c;
                } else {
                    $query = $c;
                }
            }
        }
        return $query;
    }
}
