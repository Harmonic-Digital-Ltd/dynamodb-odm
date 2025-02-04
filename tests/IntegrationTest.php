<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test;

use Aws\DynamoDb\DynamoDbClient;
use HarmonicDigital\DynamodbOdm\ItemManager;
use HarmonicDigital\DynamodbOdm\Test\Model\DateTimeObject;
use HarmonicDigital\DynamodbOdm\Test\Model\EmbeddedItem;
use HarmonicDigital\DynamodbOdm\Test\Model\TestEmbeddedObject;
use HarmonicDigital\DynamodbOdm\Test\Model\TestMultipleEmbeddedObject;
use HarmonicDigital\DynamodbOdm\Test\Model\TestObject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ItemManager::class)]
class IntegrationTest extends TestCase
{
    private ItemManager $client;

    protected function setUp(): void
    {
        $dynamoDbEndpoint = \getenv('DYNAMODB_ENDPOINT');
        $dynamoDbRegion = \getenv('DYNAMODB_REGION') ?: 'eu-west-1';

        if (!$dynamoDbEndpoint) {
            $this->markTestSkipped('DYNAMODB_ENDPOINT is not set.');
        }

        $client = new DynamoDbClient([
            'region' => $dynamoDbRegion,
            'endpoint' => $dynamoDbEndpoint,
            'version' => 'latest',
            'credentials' => [
                'key' => \getenv('DYNAMODB_ACCESS_KEY_ID') ?: '',
                'secret' => \getenv('DYNAMODB_ACCESS_KEY_SECRET') ?: '',
            ],
        ]);

        $this->client = new ItemManager($client);
    }

    public function testCreatePutAndGet(): void
    {
        $this->client->createTable(TestObject::class);
        $item = new TestObject();

        $this->client->put($item);

        $retrievedItem2 = $this->client->getItem(TestObject::class, 'id', 30);
        $this->assertEquals($item, $retrievedItem2);
        $retrievedItem2->setName('new name');
        $this->client->put($retrievedItem2);
        $retrievedItem3 = $this->client->getItem(TestObject::class, 'id', 30);
        $this->assertEquals($retrievedItem2, $retrievedItem3);
        $this->assertNotEquals($item, $retrievedItem3);
        $this->client->delete($item);
        $this->assertNull($this->client->getItem(TestObject::class, 'id', 30));
    }

    public function testCreatePutAndGetWithEmbedded(): void
    {
        $this->client->createTable(TestEmbeddedObject::class);
        $item = new TestEmbeddedObject('id', new EmbeddedItem('name', 30));
        $this->client->put($item);
        $retrievedItem2 = $this->client->getItem(TestEmbeddedObject::class, 'id');
        $this->assertEquals($item, $retrievedItem2);
        $this->assertEquals($item->embeddedItem, $retrievedItem2->embeddedItem);
        $retrievedItem2->embeddedItem->name = 'new name';
        $this->client->put($retrievedItem2);
        $retrievedItem3 = $this->client->getItem(TestEmbeddedObject::class, 'id');
        $this->assertEquals($retrievedItem2, $retrievedItem3);
        $this->assertNotEquals($item, $retrievedItem3);
        $this->client->delete($item);
    }

    public function testCreatePutAndGetWithEmbeddedCollection(): void
    {
        $this->client->createTable(TestMultipleEmbeddedObject::class);
        $item = new TestMultipleEmbeddedObject('id');
        $this->client->put($item);

        /** @var TestMultipleEmbeddedObject $retrievedItem2 */
        $retrievedItem2 = $this->client->getItem(TestMultipleEmbeddedObject::class, 'id');
        $this->assertEquals($item, $retrievedItem2);
        $this->assertEquals($item->embeddedItems->items, $retrievedItem2->embeddedItems->items);
        $retrievedItem2->embeddedItems->items[0]->name = 'new name';
        $this->assertNotEquals($item, $retrievedItem2);
        $this->client->put($retrievedItem2);
        $retrievedItem3 = $this->client->getItem(TestMultipleEmbeddedObject::class, 'id');
        $this->assertEquals($retrievedItem2, $retrievedItem3);
        $this->assertNotEquals($item, $retrievedItem3);
        $this->client->delete($item);
    }

    public function testWithDateTime(): void
    {
        $dateTime = new DateTimeObject();
        $this->client->createTable($dateTime::class);
        $this->client->put($dateTime);
        $retrievedItem = $this->client->getItem($dateTime::class, 'id');
        $this->assertEquals($dateTime, $retrievedItem);
    }
}
