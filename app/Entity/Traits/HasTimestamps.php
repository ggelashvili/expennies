<?php

declare(strict_types = 1);

namespace App\Entity\Traits;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

trait HasTimestamps
{
    #[Column(name: 'created_at')]
    private \DateTime $createdAt;

    #[Column(name: 'updated_at')]
    private \DateTime $updatedAt;

    #[PrePersist, PreUpdate]
    public function updateTimestamps(LifecycleEventArgs $args): void
    {
        if (! isset($this->createdAt)) {
            $this->createdAt = new \DateTime();
        }

        $this->updatedAt = new \DateTime();
    }
}
