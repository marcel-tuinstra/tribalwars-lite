<?php

namespace App\Collection;

use App\Entity\Resource;
use App\ValueObject\Resource\Category;
use Doctrine\Common\Collections\Collection;

/**
 * @extends ObjectCollection<int, Resource>
 */
class ResourceCollection extends ObjectCollection implements Collection
{
    protected ?string $supportedClass = Resource::class;

    /**
     * Get the first Resource by Category.
     */
    public function getResourceByCategory(Category $category): ?Resource
    {
        return $this->filter(fn(Resource $resource) => Category::equals($resource->getCategory(), $category))->first() ?: null;
    }

    public function sortedByAmount(): self
    {
        $resources = $this->toArray();

        usort($resources, function ($resourceLeft, $resourceRight) {
            return $resourceLeft->getAmount() <=> $resourceRight->getAmount();
        });

        return new self($resources);
    }
}