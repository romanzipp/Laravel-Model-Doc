<?php

namespace romanzipp\ModelDoc\Services\Tags;

use Illuminate\Database\Eloquent\Model as IlluminateModel;
use phpowermove\docblock\tags\AbstractDescriptionTag;

class MixinTag extends AbstractDescriptionTag
{
    private IlluminateModel $model;

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

        if (isset($this->model)) {
            $content .= sprintf('<%s>', get_class($this->model));
        }

        $this->setDescription($content);
    }

    public function setModelForGenerics(IlluminateModel $model): void
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
