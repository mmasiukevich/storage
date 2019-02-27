<?php

/**
 * Common storage parts.
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\Storage\Common;

use Amp\Promise;

/**
 * Query execution interface.
 */
interface QueryExecutor
{
    /**
     * Execute query.
     *
     * @psalm-param array<string, string|int|float|null> $parameters
     *
     * @param string $queryString
     * @param array  $parameters
     *
     * @throws \ServiceBus\Storage\Common\Exceptions\InvalidConfigurationOptions
     * @throws \ServiceBus\Storage\Common\Exceptions\ConnectionFailed
     * @throws \ServiceBus\Storage\Common\Exceptions\UniqueConstraintViolationCheckFailed
     * @throws \ServiceBus\Storage\Common\Exceptions\StorageInteractingFailed
     *
     * @return Promise<\ServiceBus\Storage\Common\ResultSet>
     */
    public function execute(string $queryString, array $parameters = []): Promise;
}
