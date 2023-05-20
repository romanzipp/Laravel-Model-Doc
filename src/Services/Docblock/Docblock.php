<?php

namespace romanzipp\ModelDoc\Services\Docblock;

use phootwork\collection\ArrayList;
use phootwork\collection\Map;
use phpowermove\docblock\Docblock as OriginalDocblock;
use phpowermove\docblock\TagNameComparator;
use phpowermove\docblock\tags\AbstractTag;
use romanzipp\ModelDoc\Services\Tags\EmptyLineTag;

class Docblock extends OriginalDocblock
{
    public function getSortedTags(): ArrayList
    {
        $this->comparator = $this->comparator ?? new TagNameComparator();

        // 1) group by tag name
        $group = new Map();
        /** @var AbstractTag $tag */
        foreach ($this->tags->toArray() as $tag) {
            if ( ! $group->has($tag->getTagName())) {
                $group->set($tag->getTagName(), new ArrayList());
            }

            /** @var ArrayList $list */
            $list = $group->get($tag->getTagName());
            $list->add($tag);
        }

        // 2) Sort the group by tag name
        $group->sortKeys(new TagNameComparator());

        // 3) flatten the group
        $sorted = new ArrayList();

        $groupValues = $group->values()->toArray();

        foreach ($groupValues as $index => $tags) {
            $sorted->add(...$tags);

            if ($index < count($groupValues) - 1) {
                $sorted->add(
                    new EmptyLineTag()
                );
            }
        }

        return $sorted;
    }

    protected function writeLines(array $lines, bool $newline = false): string
    {
        $docblock = '';
        if ($newline) {
            $docblock .= " *\n";
        }

        foreach ($lines as $line) {
            if (str_contains($line, "\n")) {
                $sublines = explode("\n", $line);
                $line = array_shift($sublines);
                $docblock .= rtrim(" * $line") . "\n";
                $docblock .= $this->writeLines($sublines);
            } else {
                $docblock .= rtrim(" * $line") . "\n";
            }
        }

        return $docblock;
    }
}
