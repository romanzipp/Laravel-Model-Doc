<?php

namespace romanzipp\ModelDoc\Services\Tags;

use gossi\docblock\tags\AbstractDescriptionTag;

class MixinTag extends AbstractDescriptionTag
{
    public function __construct(string $class)
    {
        parent::__construct('mixin', $class);
    }

    protected function parse(string $content): void
    {
        if ('\\' !== substr($content, 0, 1)) {
            $content = "\\$content";
        }

        $this->setDescription($content);
    }

    public function toString(): string
    {
        return trim(sprintf('@mixin %s', trim($this->description)));
    }

    public function getVariable()
    {
        return null;
    }
}
