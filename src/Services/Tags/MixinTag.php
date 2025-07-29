<?php

namespace romanzipp\ModelDoc\Services\Tags;

use phpowermove\docblock\tags\AbstractDescriptionTag;
use romanzipp\ModelDoc\Services\Objects\Model;

class MixinTag extends AbstractDescriptionTag
{
    private Model $model;

    /**
     * {@inheritdoc}
     */
    public function getTagName(): string
    {
        return 'mixin';
    }

    protected function parse(string $content): void
    {
        if ('\\' !== substr($content, 0, 1)) {
            $content = "\\$content";
        }

        if (isset($this->model) && null !== $this->model->getInstance()) {
            $content .= sprintf('<%s>', get_class($this->model->getInstance()));
        }

        $this->setDescription($content);
    }

    public function setModelForGenerics(Model $model): void
    {
        $this->model = $model;
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
