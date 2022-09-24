<?php

namespace ZnCore\Collection\Libs;

use ZnCore\Collection\Interfaces\Enumerable;

/**
 * Коллекция сущностей
 */
class Collection extends \Doctrine\Common\Collections\ArrayCollection implements Enumerable
{

    /**
     * @return $this
     * @todo move to CollectionHelper
     */
    public function reverse()
    {
        return new static(array_reverse($this->toArray(), true));
    }

    public function sortBy($key): self
    {
        $sorCallback = function ($item1, $item2) use ($key) {
            $a = $item1->{$key};
            $b = $item2->{$key};

            if ($a === $b) {
                return 0;
            }
            return $a < $b ? -1 : 1;
        };

        $iterator = $this->getIterator();
        $iterator->uasort($sorCallback);
        return new static(iterator_to_array($iterator));
//        dd($sorted);
    }
}
