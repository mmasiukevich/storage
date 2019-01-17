<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) storage component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Storage;

use Amp\Promise;

/**
 * The result of the operation
 */
interface ResultSet
{
    /**
     * Succeeds with true if an emitted value is available by calling getCurrent() or false if the iterator has
     * resolved. If the iterator fails, the returned promise will fail with the same exception.
     *
     * @return Promise<bool>
     *
     * @throws \Desperado\ServiceBus\Storage\Exceptions\ResultSetIterationFailed
     */
    public function advance(): Promise;

    /**
     * Gets the last emitted value or throws an exception if the iterator has completed
     *
     * @return array<string, string|int|null|float|resource>|null Value emitted from the iterator
     *
     * @throws \Desperado\ServiceBus\Storage\Exceptions\ResultSetIterationFailed
     */
    public function getCurrent(): ?array;

    /**
     * Receive last insert id
     *
     * @param string $sequence
     *
     * @return string|int|null
     *
     * @throws \Desperado\ServiceBus\Storage\Exceptions\ResultSetIterationFailed
     */
    public function lastInsertId(?string $sequence = null);

    /**
     * Returns the number of rows affected by the last DELETE, INSERT, or UPDATE statement executed
     *
     * @return int
     *
     * @throws \Desperado\ServiceBus\Storage\Exceptions\ResultSetIterationFailed
     */
    public function affectedRows(): int;
}