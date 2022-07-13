<?php

namespace ZnCore\Collection\Libs;

use ZnCore\Collection\Interfaces\Enumerable;

class Collection extends \Doctrine\Common\Collections\ArrayCollection implements Enumerable
{

    public function reverse()
    {
        return new static(array_reverse($this->toArray(), true));
    }
}
