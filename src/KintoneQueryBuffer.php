<?php

namespace KintoneQueryBuilder;

/**
 * internal expression of query
 * Class KintoneQueryBuffer
 * @package KintoneQueryBuilder
 */
class KintoneQueryBuffer implements KintoneQueryBufferInterface
{
    /**
     * null or 'and' or 'or'
     * @var string|null
     */
    private $conj;

    /**
     * @var KintoneQueryBufferInterface[]
     */
    private $buffer;

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
     * @return string|null
     */
    public function getConj(): ?string
    {
        return $this->conj;
    }

    /**
     * @param string|null $conj
     * @return $this
     */
    public function setConj(?string $conj): self
    {
        $this->conj = $conj;

        return $this;
    }

    /**
     * @return KintoneQueryBufferInterface[]
     */
    public function getBuffer(): array
    {
        return $this->buffer;
    }

    /**
     * @param KintoneQueryBufferInterface $obj
     */
    public function append(KintoneQueryBufferInterface $obj): void
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
            if ($i === 0) {
                $query .= $subQuery;
            } else {
                $query .= ' ' . $e->getConj() . ' ' . $subQuery;
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
