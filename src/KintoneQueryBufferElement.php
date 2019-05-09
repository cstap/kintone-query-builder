<?php

namespace KintoneQueryBuilder;

/**
 * Class KintoneQueryBufferElement
 * @package KintoneQueryBuilder
 */
class KintoneQueryBufferElement implements KintoneQueryBufferInterface
{
    /**
     * null or 'and' or 'or'
     * @var string|null
     */
    private $conj;

    /**
     * minimum element ('x < 10' or 'y = 10' or 'name like "banana"')
     * @var string
     */
    private $data;

    /**
     * KintoneQueryBufferElement constructor.
     * @param string $data
     * @param string|null $conj
     */
    public function __construct(string $data, ?string $conj = null)
    {
        $this->data = $data;
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
     * @param bool $hasParen
     * @return string
     */
    public function toQuery(bool $hasParen = false): string
    {
        // ignore $hasParen
        return $this->data;
    }
}
