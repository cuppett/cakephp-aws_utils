<?php
App::uses('Model', 'Model');
App::uses('CakeSession', 'Model/Datasource');
App::uses('DynamoDBSession', 'AwsUtils.Model/Datasource/Session');

use Aws\Common\Aws;
use Aws\Common\Enum\Region;

/**
 * DynamoDBSession session test.
 */
class DynamoDBSessionTest extends CakeTestCase
{

    protected static $_sessionBackup;

    /**
     * fixtures
     *
     * @var string
     */
    public $fixtures = array(
        'core.session'
    );

    /**
     * test case startup
     *
     * @return void
     */
    public static function setupBeforeClass()
    {
        self::$_sessionBackup = Configure::read('Session');

        // Connection to DynamoDB Local
        $aws = Aws::factory(array(
            'key' => 'XXXXXXXXXXXXXX',
            'secret' => 'yyyyyyyyyyyyyyyyyyyyyyyyyyyyy',
            'region' => 'us-east-1',
            'base_url' => 'http://localhost:8000'
        ));

        Configure::write('Session.handler', array(
            'engine' => 'AwsUtils.DynamoDBSession',
            'aws' => $aws
        ));
        Configure::write('Session.timeout', 100);

        $dynamoDB = $aws->get('dynamodb');

        $found = false;
        $response = $dynamoDB->listTables(array());
        if (! in_array(DynamoDBSession::DEFAULT_TABLE_NAME, $response['TableNames'])) {
            $handler = new DynamoDBSession();
            $aws_handler = $handler->getSessionHandler();
            $aws_handler->createSessionsTable(5, 5);
        }
    }

    /**
     * cleanup after test case.
     *
     * @return void
     */
    public static function teardownAfterClass()
    {
        Configure::write('Session', self::$_sessionBackup);
    }

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->storage = new DynamoDBSession();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->storage);
        ClassRegistry::flush();
        parent::tearDown();
    }

    /**
     * test that constructor sets the right things up.
     *
     * @return void
     */
    public function testConstructionSettings()
    {
        ClassRegistry::flush();
    }

    /**
     * test opening the session
     *
     * @return void
     */
    public function testOpen()
    {
        $this->assertTrue($this->storage->open());
    }

    /**
     * test write()
     *
     * @return void
     */
    public function testWrite()
    {
        $result = $this->storage->write('foo', 'Some value');
        $this->assertNotNull($result);
    }

    /**
     * testReadAndWriteWithDatabaseStorage method
     *
     * @return void
     */
    public function testWriteEmptySessionId()
    {
        $result = $this->storage->write('', 'This is a Test');
        $this->assertFalse($result);
    }

    /**
     * test read()
     *
     * @return void
     */
    public function testRead()
    {
        $this->storage->write('foo', 'Some value');

        $result = $this->storage->read('foo');
        $expected = 'Some value';
        $this->assertEquals($expected, $result);

        $result = $this->storage->read('made up value');
        $this->assertFalse($result);
    }

    /**
     * test blowing up the session.
     *
     * @return void
     */
    public function testDestroy()
    {
        $this->storage->write('foo', 'Some value');

        $this->assertTrue($this->storage->destroy('foo'), 'Destroy failed');
        $this->assertFalse($this->storage->read('foo'), 'Value still present.');
    }

    /**
     * test the garbage collector
     *
     * @return void
     */
    public function testGc()
    {
        ClassRegistry::flush();
        Configure::write('Session.timeout', 0);

        $storage = new DynamoDBSession();
        $storage->write('foo', 'Some value');

        sleep(1);
        $storage->gc();
        $this->assertFalse($storage->read('foo'));
    }
}
