<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Service;

use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Facade\Logger;

/**
 * Schedules a migration across many records without loading them all at once.
 *
 * A migration that has to touch every order or every product cannot run inline: a large shop would
 * time out. Instead the work is split into chunks, each handed to the cron service as a separate task,
 * staggered so they do not all fire at the same moment.
 *
 * Finding the records stays with the caller, because the query is platform-specific — orders and
 * products are fetched in completely different ways. Pass a fetcher that returns one page of ids at a
 * time and this service takes care of paging until the fetcher runs dry, plus the scheduling and the
 * logging.
 *
 * The scheduled action must be registered by the platform, because the callback has to survive into a
 * later request. That is why a timestamped migration can schedule work here but cannot be the callback
 * itself: it is an anonymous class and nothing can address it later.
 */
class PagedMigrationService
{
    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CronServiceInterface
     */
    private $cronService;

    public function __construct(CronServiceInterface $cronService)
    {
        $this->cronService = $cronService;
    }

    /**
     * Schedule $cronAction once per page of ids, until the fetcher returns an empty page.
     *
     * Each scheduled task receives one argument: an array holding the page's ids under "ids" and the
     * 1-based chunk number under "chunk".
     *
     * @param  string   $cronAction           Registered action the platform dispatches per chunk
     * @param  callable $fetchPage            fn(int $page, int $pageSize): int[]
     * @param  int      $pageSize             How many records per chunk
     * @param  int      $secondsBetweenChunks Delay added per chunk, so they do not all fire at once
     *
     * @return int The number of chunks scheduled
     */
    public function schedulePages(
        string   $cronAction,
        callable $fetchPage,
        int      $pageSize = 100,
        int      $secondsBetweenChunks = 5
    ): int {
        $page   = 1;
        $chunks = 0;
        $now    = time();

        while (true) {
            $ids = $fetchPage($page, $pageSize);

            if (empty($ids)) {
                break;
            }

            $chunkNumber = $chunks + 1;
            $timestamp   = $now + $chunks * $secondsBetweenChunks;

            $this->cronService->schedule($cronAction, $timestamp, [
                'ids'   => $ids,
                'chunk' => $chunkNumber,
            ]);

            Logger::debug('Scheduled migration chunk', [
                'action'    => $cronAction,
                'chunk'     => $chunkNumber,
                'records'   => count($ids),
                'timestamp' => $timestamp,
            ]);

            $chunks++;
            $page++;

            // A page that came back short is the last one, so stop rather than asking for another.
            if (count($ids) < $pageSize) {
                break;
            }
        }

        return $chunks;
    }
}
