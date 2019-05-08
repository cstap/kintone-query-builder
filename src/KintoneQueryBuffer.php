<?php

namespace KintoneQueryBuilder;

/**
 * internal expression of query
 * Class KintoneQueryBuffer
 * @package KintoneQueryBuilder
 */

class KintoneQueryBuffer
{
    /**
     * null or 'and' or 'or'
     * @var string|null
     */
    public $conj;

    /**
     * @var (KintoneQueryBuffer|KintoneQueryBuilder)[]
     */
    public $buffer;

    /**
     * KintoneQueryBuffer constructor.
     * @param string|null $conj
     */
    public function __construct(?string $conj = null)
    {
        $this->buffer = [];
        $this->conj = $conj;
    }

    /**
     * @param KintoneQueryBuffer|KintoneQueryBufferElement $obj
     */
    public function append($obj): void
    {
        $this->buffer[] = $obj;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->buffer === [];
    }

    /**
     * @param bool $hasParen
     * @return string
     */
    public function toQuery(bool $hasParen = false): string
    {
        $query = '';
        foreach ($this->buffer as $i => $e) {
            // $e instanceof KintoneQueryBuffer || $e instanceof KintoneQueryBufferElement
            $subQuery = $e->toQuery(true);
            if ($subQuery === '()' || $subQuery === '') {
                continue;
            }
            if ($i == 0) {
                $query .= $subQuery;
            } else {
                $query .= ' ' . $e->conj . ' ' . $subQuery;
            }
        }
        if ($query === '') {
            return '';
        }
        if ($hasParen) {
            return '(' . $query . ')';
        }
        return $query;
    }
}
