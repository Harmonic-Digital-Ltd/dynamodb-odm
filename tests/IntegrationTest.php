<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test;

use Aws\DynamoDb\DynamoDbClient;
use HarmonicDigital\DynamodbOdm\Client;
use HarmonicDigital\DynamodbOdm\Test\Model\EmbeddedItem;
use HarmonicDigital\DynamodbOdm\Test\Model\TestEmbeddedObject;
use HarmonicDigital\DynamodbOdm\Test\Model\TestObject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Client::class)]
class IntegrationTest extends TestCase
{
    private Client $client;

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

        $this->client = new Client($client);
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
}
