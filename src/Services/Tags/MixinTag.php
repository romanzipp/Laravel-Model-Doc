<?php

namespace romanzipp\ModelDoc\Services\Tags;

use phpowermove\docblock\tags\AbstractDescriptionTag;

class MixinTag extends AbstractDescriptionTag
{
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

    public function getVariable(): ?string
    {
        return null;
    }
}
