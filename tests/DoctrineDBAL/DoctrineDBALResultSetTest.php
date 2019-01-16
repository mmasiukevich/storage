<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) storage component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Storage\Tests\DoctrineDBAL;

use function Amp\Promise\wait;
use Desperado\ServiceBus\Storage\DoctrineDBAL\DoctrineDBALAdapter;
use function Desperado\ServiceBus\Storage\fetchAll;
use function Desperado\ServiceBus\Storage\fetchOne;
use Desperado\ServiceBus\Storage\StorageConfiguration;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;

/**
 *
 */
final class DoctrineDBALResultSetTest extends TestCase
{
    /**
     * @var DoctrineDBALAdapter
     */
    private $adapter;

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->adapter = new DoctrineDBALAdapter(
            StorageConfiguration::fromDSN('sqlite:///:memory:')
        );

        wait(
            $this->adapter->execute(
                'CREATE TABLE IF NOT EXISTS test_result_set (id varchar PRIMARY KEY, value VARCHAR)'
            )
        );
    }

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        wait(
            $this->adapter->execute('DROP TABLE test_result_set')
        );
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function fetchOne(): void
    {
        $promise = $this->adapter->execute(
            'INSERT INTO test_result_set (id, value) VALUES (?,?), (?,?)', [
                'uuid1', 'value1',
                'uuid2', 'value2'
            ]
        );

        wait($promise);

        $result = wait(
            fetchOne(
                wait($this->adapter->execute('SELECT * FROM test_result_set WHERE id = \'uuid2\''))
            )
        );

        static::assertNotEmpty($result);
        static:: assertEquals(['id' => 'uuid2', 'value' => 'value2'], $result);

        $result = wait(
            fetchOne(
                wait($this->adapter->execute('SELECT * FROM test_result_set WHERE id = \'uuid4\''))
            )
        );

        static::assertNull($result);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function fetchAll(): void
    {
        $promise = $this->adapter->execute(
            'INSERT INTO test_result_set (id, value) VALUES (?,?), (?,?)', [
                'uuid1', 'value1',
                'uuid2', 'value2'
            ]
        );

        wait($promise);

        $result = wait(
            fetchAll(
                wait($this->adapter->execute('SELECT * FROM test_result_set'))
            )
        );

        static::assertNotEmpty($result);
        static::assertCount(2, $result);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function fetchAllWithEmptySet(): void
    {
        $result = wait(
            fetchAll(
                wait($this->adapter->execute('SELECT * FROM test_result_set'))
            )
        );

        static::assertThat($result, new IsType('array'));
        static::assertEmpty($result);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function multipleGetCurrentRow(): void
    {
        $promise = $this->adapter->execute(
            'INSERT INTO test_result_set (id, value) VALUES (?,?), (?,?)', [
                'uuid1', 'value1',
                'uuid2', 'value2'
            ]
        );

        wait($promise);

        /** @var \Desperado\ServiceBus\Storage\ResultSet $result */
        $result = wait($this->adapter->execute('SELECT * FROM test_result_set'));

        while(wait($result->advance()))
        {
            $row     = $result->getCurrent();
            $rowCopy = $result->getCurrent();

            static::assertEquals($row, $rowCopy);
        }
    }
}
