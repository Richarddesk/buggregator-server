<?php

declare(strict_types=1);

namespace Modules\Profiler\Application;

use App\Application\Event\EventTypeRegistryInterface;
use Modules\Profiler\Application\Handlers\CalculateDiffsBetweenEdges;
use Modules\Profiler\Application\Handlers\CleanupEvent;
use Modules\Profiler\Application\Handlers\PrepareEdges;
use Modules\Profiler\Application\Handlers\PreparePeaks;
use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;

final class ProfilerBootloader extends Bootloader
{
    public function defineSingletons(): array
    {
        return [
            EventHandlerInterface::class => static fn(
                ContainerInterface $container,
            ): EventHandlerInterface => new EventHandler($container, [
                PreparePeaks::class,
                CalculateDiffsBetweenEdges::class,
                PrepareEdges::class,
                CleanupEvent::class,
            ]),
        ];
    }

    public function boot(EventTypeRegistryInterface $registry): void
    {
        $registry->register('profiler', new Mapper\EventTypeMapper());
    }
}
