<?php

namespace JorisVaesen\KeyValuePairs\Test\TestCase\Model\Behavior;

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior;
use JorisVaesen\KeyValuePairs\Test\Fixture\ConfigsFixture;

class KeyValuePairBehaviorTest extends TestCase
{
    public $fixtures = ['plugin.joris_vaesen\key_value_pairs.configs'];
    public $autoFixtures = false;

    private $tableMock;
    private $table;
    private $entity;
    private $behaviorMethods;

    public function setUp()
    {
        parent::setUp();

        $this->tableMock = $this->getMock('Cake\ORM\Table');
        $this->behaviorMethods = get_class_methods('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior');
        $this->entity = new Entity([
            'key' => 'key1',
            'value' => 'value1'
        ]);
        $this->table = new Table([
            'table' => 'configs',
            'alias' => 'Configs',
            'schema' => (new ConfigsFixture())->fields,
            'connection' => ConnectionManager::get('test')
        ]);
    }

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
        Cache::clear();
    }

    public function testBeforeSaveAllowedKeysFalse()
    {
        $settings = [
            'allowedKeys' => false
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'beforeSave']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->tableMock, $settings]);

        $this->assertNull($behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject));
    }

    public function testBeforeSaveAllowedKeysArray()
    {
        $settings = [
            'allowedKeys' => ['key5', 'key6', 'key7']
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'beforeSave']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->tableMock, $settings]);

        $this->assertFalse($behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject));
    }

    public function testBeforeSaveAllowedKeysArrayContainsKey()
    {
        $settings = [
            'allowedKeys' => ['key1', 'key2', 'key3']
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'beforeSave']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->tableMock, $settings]);

        $this->assertNull($behavior->beforeSave(new Event('fake.event'), $this->entity, new ArrayObject));
    }

    public function testAfterSave()
    {
        $settings = [
            'cache' => true,
            'cacheConfig' => 'configs'
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'afterSave']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->tableMock, $settings]);
        Cache::write('key_value_pairs_' . $this->tableMock->table(), 'sample value', $settings['cacheConfig']);
        $behavior->afterSave(new Event('fake.event'), $this->entity, new ArrayObject);

        $this->assertFalse(Cache::read('key_value_pairs_' . $this->tableMock->table(), $settings['cacheConfig']));
    }

    public function testAfterSaveCacheKey()
    {
        $settings = [
            'cache' => true,
            'cacheConfig' => 'configs'
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'afterSave']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->tableMock, $settings]);
        Cache::write('key_value_pairs_' . $this->tableMock->table(), 'sample value', 'default');
        $behavior->afterSave(new Event('fake.event'), $this->entity, new ArrayObject);

        $this->assertEquals(Cache::read('key_value_pairs_' . $this->tableMock->table(), 'default'), 'sample value');
    }

    public function testBeforeDeleteWithPreventDeletionFalse()
    {
        $settings = [
            'preventDeletion' => false
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'beforeDelete']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->tableMock, $settings]);

        $this->assertNull($behavior->beforeDelete(new Event('fake.event'), $this->entity, new ArrayObject));
    }

    public function testBeforeDeleteWithPreventDeletionTrue()
    {
        $settings = [
            'preventDeletion' => true
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'beforeDelete']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->tableMock, $settings]);

        $this->assertFalse($behavior->beforeDelete(new Event('fake.event'), $this->entity, new ArrayObject));
    }

    public function testBeforeDeleteWithPreventDeletionArray()
    {
        $settings = [
            'preventDeletion' => ['key5', 'key6', 'key7']
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'beforeDelete']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->tableMock, $settings]);

        $this->assertNull($behavior->beforeDelete(new Event('fake.event'), $this->entity, new ArrayObject));
    }

    public function testBeforeDeleteWithPreventDeletionArrayContainsKey()
    {
        $settings = [
            'preventDeletion' => ['key1', 'key2', 'key3']
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'beforeDelete']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->tableMock, $settings]);

        $this->assertFalse($behavior->beforeDelete(new Event('fake.event'), $this->entity, new ArrayObject));
    }

    public function testAfterDelete()
    {
        $settings = [
            'cache' => true,
            'cacheConfig' => 'configs'
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'afterDelete']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->tableMock, $settings]);
        Cache::write('key_value_pairs_' . $this->tableMock->table(), 'sample value', $settings['cacheConfig']);
        $behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject);

        $this->assertFalse(Cache::read('key_value_pairs_' . $this->tableMock->table(), $settings['cacheConfig']));
    }

    public function testAfterDeleteCacheKey()
    {
        $settings = [
            'cache' => true,
            'cacheConfig' => 'configs'
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'afterDelete']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->tableMock, $settings]);
        Cache::write('key_value_pairs_' . $this->tableMock->table(), 'sample value', 'default');
        $behavior->afterDelete(new Event('fake.event'), $this->entity, new ArrayObject);

        $this->assertEquals(Cache::read('key_value_pairs_' . $this->tableMock->table(), 'default'), 'sample value');
    }

    public function testFindPair()
    {
        $this->loadFixtures('Configs');
        $methods = array_diff($this->behaviorMethods, ['config', 'findPair']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->table, []]);
        $this->assertEquals('INV-2016', $behavior->findPair('invoice_prefix'));
    }

    public function testFindPairAsEntity()
    {
        $this->loadFixtures('Configs');
        $methods = array_diff($this->behaviorMethods, ['config', 'findPair']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->table, []]);
        $pair = $behavior->findPair('invoice_prefix', true);

        $this->assertInstanceOf('Cake\ORM\Entity', $pair);
        $this->assertEquals('INV-2016', $pair->{'value'});
    }

    public function testFindPairNotExistingKey()
    {
        $this->loadFixtures('Configs');
        $methods = array_diff($this->behaviorMethods, ['config', 'findPair']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->table, []]);
        $this->assertFalse($behavior->findPair('not_existing_key'));
    }

    public function testFindPairWithCacheEnabled()
    {
        $this->loadFixtures('Configs');
        $settings = [
            'cache' => true
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'findPair']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->table, $settings]);

        $this->assertEquals('INV-2016', $behavior->findPair('invoice_prefix'));
    }

    public function testFindPairs()
    {
        $this->loadFixtures('Configs');
        $methods = array_diff($this->behaviorMethods, ['config', 'findPairs']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->table, []]);
        $expected = ['invoice_prefix' => 'INV-2016', 'invoice_next_number' => '1234'];
        $this->assertEquals($expected, $behavior->findPairs(['invoice_prefix', 'invoice_next_number']));
    }

    public function testFindPairsAsEntities()
    {
        $this->loadFixtures('Configs');
        $methods = array_diff($this->behaviorMethods, ['config', 'findPairs']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->table, []]);

        $pairs = $behavior->findPairs(['invoice_prefix', 'invoice_next_number'], true, true);

        $this->assertInstanceOf('Cake\ORM\Entity', $pairs['invoice_prefix']);
        $this->assertInstanceOf('Cake\ORM\Entity', $pairs['invoice_next_number']);
        $this->assertEquals('INV-2016', $pairs['invoice_prefix']->value);
        $this->assertEquals('1234', $pairs['invoice_next_number']->value);
    }

    public function testFindPairsNotExistingKeys()
    {
        $this->loadFixtures('Configs');
        $methods = array_diff($this->behaviorMethods, ['config', 'findPairs']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->table, []]);
        $this->assertFalse($behavior->findPairs(['not_existing_key1', 'not_existing_key2']));
    }

    public function testFindPairsRequireAllOk()
    {
        $this->loadFixtures('Configs');
        $methods = array_diff($this->behaviorMethods, ['config', 'findPairs']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->table, []]);
        $expected = ['invoice_prefix' => 'INV-2016', 'invoice_next_number' => '1234'];
        $this->assertEquals($expected, $behavior->findPairs(['invoice_prefix', 'invoice_next_number'], true));
    }

    public function testFindPairsRequireAllFail()
    {
        $this->loadFixtures('Configs');
        $methods = array_diff($this->behaviorMethods, ['config', 'findPairs']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->table, []]);
        $this->assertFalse($behavior->findPairs(['invoice_prefix', 'not_existing_key1'], true));
    }

    public function testFindPairsWithCacheEnabled()
    {
        $this->loadFixtures('Configs');
        $settings = [
            'cache' => true
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'findPairs']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->table, $settings]);
        $expected = ['invoice_prefix' => 'INV-2016', 'invoice_next_number' => '1234'];
        $this->assertEquals($expected, $behavior->findPairs(['invoice_prefix', 'invoice_next_number']));
    }

    public function testFindPairsNotExistingKeysWithCacheEnabled()
    {
        $this->loadFixtures('Configs');
        $settings = [
            'cache' => true
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'findPairs']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->table, $settings]);
        $this->assertFalse($behavior->findPairs(['not_existing_key1', 'not_existing_key2']));
    }

    public function testFindPairsRequireAllOkWithCacheEnabled()
    {
        $this->loadFixtures('Configs');
        $settings = [
            'cache' => true
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'findPairs']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->table, $settings]);
        $expected = ['invoice_prefix' => 'INV-2016', 'invoice_next_number' => '1234'];
        $this->assertEquals($expected, $behavior->findPairs(['invoice_prefix', 'invoice_next_number'], true));
    }

    public function testFindPairsRequireAllFailWithCacheEnabled()
    {
        $this->loadFixtures('Configs');
        $settings = [
            'cache' => true
        ];
        $methods = array_diff($this->behaviorMethods, ['config', 'findPairs']);
        $behavior = $this->getMock('JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior', $methods, [$this->table, $settings]);
        $this->assertFalse($behavior->findPairs(['invoice_prefix', 'not_existing_key1'], true));
    }

    public function testQueryBuilder()
    {
        $method = new \ReflectionMethod(
            'JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior',
            '_queryBuilder'
        );
        $method->setAccessible(true);
        $query = $method->invoke(new KeyValuePairsBehavior($this->table, []));
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertNull($query->clause('where'));
    }

    public function testQueryBuilderWithScope()
    {
        $this->loadFixtures('Configs');
        $settings = [
            'scope' => [
                'deleted' => 1
            ]
        ];
        $method = new \ReflectionMethod(
            'JorisVaesen\KeyValuePairs\Model\Behavior\KeyValuePairsBehavior',
            '_queryBuilder'
        );
        $method->setAccessible(true);
        $query = $method->invoke(new KeyValuePairsBehavior($this->table, $settings));
        $this->assertInstanceOf('Cake\ORM\Query', $query);
        $this->assertEquals(2, $query->count());
        $this->assertContains('deleted', $query->sql());
    }
}
