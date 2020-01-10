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

use ServiceBus\Storage\Common\Exceptions\InvalidConfigurationOptions;

/**
 * Adapter configuration for storage.
 *
 * @psalm-readonly
 */
final class StorageConfiguration
{
    /**
     * Original DSN.
     *
     * @var string
     */
    public $originalDSN;

    /**
     * Scheme.
     *
     * @var string|null
     */
    public $scheme;

    /**
     * Database host.
     *
     * @var string|null
     */
    public $host = null;

    /**
     * Database port.
     *
     * @var int|null
     */
    public $port = null;

    /**
     * Database user.
     *
     * @var string|null
     */
    public $username;

    /**
     * Database user password.
     *
     * @var string|null
     */
    public $password = null;

    /**
     * Database name.
     *
     * @var string|null
     */
    public $databaseName = null;

    /**
     * Connection encoding.
     *
     * @var string|null
     */
    public $encoding;

    /**
     * All query parameters.
     *
     * @var array
     */
    public $queryParameters = [];

    /**
     * @param string $connectionDSN DSN examples:
     *                              - inMemory: sqlite:///:memory:
     *                              - AsyncPostgreSQL: pgsql://user:password@host:port/database
     *
     * @throws \ServiceBus\Storage\Common\Exceptions\InvalidConfigurationOptions
     */
    public function __construct(string $connectionDSN)
    {
        $preparedDSN = \preg_replace('#^((?:pdo_)?sqlite3?):///#', '$1://localhost/', $connectionDSN);

        /**
         * @psalm-var array{
         *    scheme:string|null,
         *    host:string|null,
         *    port:int|null,
         *    user:string|null,
         *    pass:string|null,
         *    path:string|null
         * }|null|false $parsedDSN
         *
         * @var array|false|null $parsedDSN
         */
        $parsedDSN = \parse_url((string) $preparedDSN);

        // @codeCoverageIgnoreStart
        if (\is_array($parsedDSN) === false)
        {
            throw new InvalidConfigurationOptions('Error while parsing connection DSN');
        }
        // @codeCoverageIgnoreEnd

        $queryString = 'charset=UTF-8';

        if (isset($parsedDSN['query']) === true && '' !== $parsedDSN['query'])
        {
            $queryString = (string) $parsedDSN['query'];
        }

        \parse_str($queryString, $this->queryParameters);

        /** @var array{charset:string|null, max_connections:int|null, idle_timeout:int|null} $queryParameters */
        $queryParameters = $this->queryParameters;

        $this->originalDSN  = $connectionDSN;
        $this->scheme       = self::stringOrNull('scheme', $parsedDSN);
        $this->host         = self::stringOrNull('host', $parsedDSN, 'localhost');
        $this->port         = $parsedDSN['port'] ?? null;
        $this->username     = self::stringOrNull('user', $parsedDSN);
        $this->password     = self::stringOrNull('pass', $parsedDSN);
        $this->databaseName = $parsedDSN['path'] ? \ltrim((string) $parsedDSN['path'], '/') : null;
        $this->encoding     = $queryParameters['charset'] ?? 'UTF-8';
    }

    /**
     * Has specified credentials.
     */
    public function hasCredentials(): bool
    {
        return (string) $this->username !== '' || (string) $this->password !== '';
    }

    private static function stringOrNull(string $key, array $collection, $default = null): ?string
    {
        if (\array_key_exists($key, $collection) === false)
        {
            return $default;
        }

        /** @var string|null $value */
        $value = $collection[$key];

        if ($value === null || $value === '')
        {
            return $default;
        }

        return $value;
    }
}
