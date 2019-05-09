<?php

namespace KintoneQueryBuilder;

/**
 * @author ochi51 <ochiai07@gmail.com>
 */
interface KintoneQueryBufferInterface
{
    /**
     * @return string|null
     */
    public function getConj(): ?string;

    /**
     * @param bool $hasParen
     * @return string
     */
    public function toQuery(bool $hasParen = false): string;
}
