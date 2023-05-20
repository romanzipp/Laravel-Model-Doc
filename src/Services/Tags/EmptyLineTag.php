<?php

namespace romanzipp\ModelDoc\Services\Tags;

use phpowermove\docblock\tags\AbstractDescriptionTag;

class EmptyLineTag extends AbstractDescriptionTag
{
    public function toString(): string
    {
        return '';
    }

    protected function parse(string $content): void
    {
    }
}
