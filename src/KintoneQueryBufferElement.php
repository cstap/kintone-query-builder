<?php

namespace KintoneQueryBuilder;

/**
 * Class KintoneQueryBufferElement
 * @package KintoneQueryBuilder
 */

class KintoneQueryBufferElement
{
    /**
     * null or 'and' or 'or'
     * @var string|null
     */
    public $conj;

    /**
     * minimum element ('x < 10' or 'y = 10' or 'name like "banana"')
     * @var string
     */
    public $data;

    /**
     * KintoneQueryBufferElement constructor.
     * @param string $data
     * @param string|null $conj
     */
    public function __construct(string $data, string $conj = null)
    {
        $this->data = $data;
        $this->conj = $conj;
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
