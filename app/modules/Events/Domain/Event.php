<?php

declare(strict_types=1);

namespace Modules\Events\Domain;

use App\Application\Domain\Entity\Json;
use App\Application\Domain\ValueObjects\Uuid;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;

#[Entity(
    repository: EventRepositoryInterface::class
)]
#[Index(columns: ['type'])]
#[Index(columns: ['project'])]
class Event
{
    /**  @internal */
    public function __construct(
        #[Column(type: 'string(36)', primary: true, typecast: 'uuid')]
        private Uuid $uuid,

        #[Column(type: 'string(50)')]
        private string $type,

        #[Column(type: 'jsonb', typecast: Json::class)]
        private Json $payload,

        #[Column(type: 'float')]
        private float $timestamp,

        #[Column(type: 'string', nullable: true)]
        private ?string $project = null,
    ) {
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPayload(): Json
    {
        return $this->payload;
    }

    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    public function getProject(): ?string
    {
        return $this->project;
    }
}
