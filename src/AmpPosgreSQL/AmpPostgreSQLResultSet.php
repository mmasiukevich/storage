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

use Amp\Postgres\PgSqlCommandResult;
use Amp\Postgres\PqCommandResult;
use Amp\Promise;
use Amp\Success;
use Amp\Sql\ResultSet as AmpResultSet;
use Amp\Postgres\PooledResultSet;
use Desperado\ServiceBus\Storage\Exceptions\ResultSetIterationFailed;
use Desperado\ServiceBus\Storage\ResultSet;

/**
 *
 */
class AmpPostgreSQLResultSet implements ResultSet
{
    /**
     * @var AmpResultSet|PooledResultSet
     */
    private $originalResultSet;

    /**
     * @noinspection   PhpDocSignatureInspection
     * @psalm-suppress TypeCoercion Assume a different data type
     *
     * @param AmpResultSet|PooledResultSet $originalResultSet
     */
    public function __construct(object $originalResultSet)
    {
        $this->originalResultSet = $originalResultSet;
    }

    /**
     * @inheritdoc
     */
    public function advance(): Promise
    {
        try
        {
            if($this->originalResultSet instanceof AmpResultSet)
            {
                return $this->originalResultSet->advance();
            }

            return new Success(false);
        }
            // @codeCoverageIgnoreStart
        catch(\Throwable $throwable)
        {
            throw new ResultSetIterationFailed($throwable->getMessage(), (int) $throwable->getCode(), $throwable);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @inheritdoc
     */
    public function getCurrent(): ?array
    {
        try
        {
            return $this->originalResultSet->getCurrent();
        }
            // @codeCoverageIgnoreStart
        catch(\Throwable $throwable)
        {
            throw new ResultSetIterationFailed($throwable->getMessage(), (int) $throwable->getCode(), $throwable);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @inheritdoc
     */
    public function lastInsertId(?string $sequence = null): ?string
    {
        try
        {
            if($this->originalResultSet instanceof PooledResultSet)
            {
                /** @var array<string, mixed> $result */
                $result = $this->originalResultSet->getCurrent();

                if(0 !== \count($result))
                {
                    /** @var bool|int|string $value */
                    $value = \reset($result);

                    return false !== $value ? (string) $value : null;
                }
            }

            return null;
        }
            // @codeCoverageIgnoreStart
        catch(\Throwable $throwable)
        {
            throw new ResultSetIterationFailed($throwable->getMessage(), (int) $throwable->getCode(), $throwable);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @inheritdoc
     */
    public function affectedRows(): int
    {
        try
        {
            if(
                $this->originalResultSet instanceof PgSqlCommandResult ||
                $this->originalResultSet instanceof PqCommandResult
            )
            {
                return $this->originalResultSet->getAffectedRowCount();
            }

            return 0;
        }
            // @codeCoverageIgnoreStart
        catch(\Throwable $throwable)
        {
            throw new ResultSetIterationFailed($throwable->getMessage(), (int) $throwable->getCode(), $throwable);
        }
        // @codeCoverageIgnoreEnd
    }
}
