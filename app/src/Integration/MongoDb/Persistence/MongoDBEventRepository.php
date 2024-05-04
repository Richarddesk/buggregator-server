<?php

declare(strict_types=1);

namespace App\Integration\MongoDb\Persistence;

use App\Application\Domain\Entity\Json;
use App\Application\Domain\ValueObjects\Uuid;
use Modules\Events\Domain\Event;
use Modules\Events\Domain\EventRepositoryInterface;
use Modules\Events\Domain\ValueObject\Timestamp;
use Modules\Projects\Domain\ValueObject\Key;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;

final readonly class MongoDBEventRepository implements EventRepositoryInterface
{
    public function __construct(
        private Collection $collection,
    ) {}

    public function store(Event $event): bool
    {
        if ($this->findByPK($event->getUuid()) !== null) {
            $this->collection->replaceOne(
                ['_id' => (string) $event->getUuid()],
                [
                    '_id' => (string) $event->getUuid(),
                    'type' => $event->getType(),
                    'project' => $event->getProject() ? (string) $event->getProject() : null,
                    'timestamp' => (string) $event->getTimestamp(),
                    'payload' => $event->getPayload()->jsonSerialize(),
                ],
            );

            return true;
        }

        $result = $this->collection->insertOne([
            '_id' => (string) $event->getUuid(),
            'type' => $event->getType(),
            'project' => $event->getProject() ? (string) $event->getProject() : null,
            'timestamp' => (string) $event->getTimestamp(),
            'payload' => $event->getPayload()->jsonSerialize(),
        ]);

        return $result->getInsertedCount() > 0;
    }

    public function deleteAll(array $scope = []): void
    {
        $this->collection->deleteMany($this->buildScope($scope));
    }

    public function deleteByPK(string $uuid): bool
    {
        $deleteResult = $this->collection->deleteOne(['_id' => $uuid]);

        return $deleteResult->getDeletedCount() > 0;
    }

    public function countAll(array $scope = []): int
    {
        return $this->collection->countDocuments($this->buildScope($scope));
    }

    public function findAll(array $scope = [], array $orderBy = [], int $limit = 30, int $offset = 0): iterable
    {
        $cursor = $this->collection->find($this->buildScope($scope), [
            'sort' => $this->mapOrderBy($orderBy),
            'limit' => $limit,
            'skip' => $offset,
        ]);

        foreach ($cursor as $document) {
            yield $this->mapDocumentIntoEvent($document);
        }
    }

    /**
     * @psalm-suppress ParamNameMismatch
     */
    public function findByPK(mixed $uuid): ?Event
    {
        return $this->findOne(['_id' => (string) $uuid]);
    }

    public function findOne(array $scope = []): ?Event
    {
        /** @var BSONDocument|null $document */
        $document = $this->collection->findOne($this->buildScope($scope));

        if ($document === null) {
            return null;
        }

        return $this->mapDocumentIntoEvent($document);
    }

    public function mapDocumentIntoEvent(BSONDocument $document): Event
    {
        /** @psalm-suppress InternalMethod */
        return new Event(
            uuid: Uuid::fromString($document['_id']),
            type: $document['type'],
            payload: new Json((array) $document['payload']),
            timestamp: new Timestamp($document['timestamp']),
            project: $document['project'] ? new Key($document['project']) : null,
        );
    }

    private function mapOrderBy(array $orderBy): array
    {
        $result = [];

        foreach ($orderBy as $key => $order) {
            $result[$key] = $order === 'asc' ? 1 : -1;
        }

        return $result;
    }

    private function buildScope(array $scope): array
    {
        $newScope = [];

        foreach ($scope as $key => $value) {
            if ($key === 'uuid') {
                $key = '_id';
            }
            $newScope[$key] = \is_array($value) ? ['$in' => $value] : $value;
        }

        return $newScope;
    }
}
