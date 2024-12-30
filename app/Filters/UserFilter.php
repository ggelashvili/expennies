<?php

declare(strict_types=1);

namespace App\Filters;

use App\Contracts\OwnableInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class UserFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        // Check if the entity implements the LocalAware interface
        if (!$targetEntity->reflClass->implementsInterface(OwnableInterface::class)) {
            return "";
        }

        return $targetTableAlias . '.user_id = ' . $this->getParameter('user_id');
    }
}