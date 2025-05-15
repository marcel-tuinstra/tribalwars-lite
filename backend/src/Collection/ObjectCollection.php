<?php

namespace App\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;

abstract class ObjectCollection extends ArrayCollection
{
    protected ?string $supportedClass = null;

    public function __construct(array $elements = [])
    {
        foreach ($elements as $element) {
            $this->isSupported($element);
        }

        parent::__construct($elements);
    }

    public function add($element): void
    {
        $this->isSupported($element);

        parent::add($element);
    }

    public function isSupported(object $element): void
    {
        if (!$element instanceof $this->supportedClass) {
            throw new InvalidArgumentException(sprintf('Only objects of type [%s] are supported, not [%s]', $this->supportedClass, $element::class));
        }
    }

    public function merge(Collection $collection): void
    {
        foreach ($collection as $object) {
            $this->add($object);
        }
    }
}