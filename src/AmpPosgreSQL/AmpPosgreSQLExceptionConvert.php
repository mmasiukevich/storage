<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) storage component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Storage\AmpPosgreSQL;
use Amp\Postgres\QueryExecutionError;
use Amp\Sql\ConnectionException;
use Desperado\ServiceBus\Storage\Exceptions as InternalExceptions;

/**
 *Convert library exceptions to internal types
 *
 * @internal
 */
final class AmpPosgreSQLExceptionConvert
{
    /**
     * Convert AmPHP exceptions
     *
     * @param \Throwable $throwable
     *
     * @return InternalExceptions\UniqueConstraintViolationCheckFailed|InternalExceptions\ConnectionFailed|InternalExceptions\StorageInteractingFailed
     */
    public static function do(\Throwable $throwable): \Throwable
    {
        if(
            $throwable instanceof QueryExecutionError &&
            true === \in_array((int) $throwable->getDiagnostics()['sqlstate'], [23503, 23505], true)
        )
        {
            return new InternalExceptions\UniqueConstraintViolationCheckFailed(
                $throwable->getMessage(),
                (int) $throwable->getCode(),
                $throwable
            );
        }

        if($throwable instanceof ConnectionException)
        {
            return new InternalExceptions\ConnectionFailed(
                $throwable->getMessage(),
                (int) $throwable->getCode(),
                $throwable
            );
        }

        return new InternalExceptions\StorageInteractingFailed(
            $throwable->getMessage(),
            (int) $throwable->getCode(),
            $throwable
        );
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {

    }
}
