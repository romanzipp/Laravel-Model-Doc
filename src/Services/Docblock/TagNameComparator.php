<?php

namespace romanzipp\ModelDoc\Services\Docblock;

use phpowermove\docblock\TagNameComparator as OriginalTagNameComparator;

class TagNameComparator extends OriginalTagNameComparator
{
    /**
     * @var array<string>
     */
    private array $order;

    public function __construct()
    {
        $this->order = config('model-doc.tag_sorting');
    }

	public function compare($a, $b): int
    {
		if ($a == $b) {
			return 0;
		}

		if (!in_array($a, $this->order)) {
			return -1;
		}

		if (!in_array($b, $this->order)) {
			return 1;
		}

		$pos1 = array_search($a, $this->order);
		$pos2 = array_search($b, $this->order);

		return $pos1 < $pos2 ? -1 : 1;
	}
}
