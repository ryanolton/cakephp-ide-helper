<?php

namespace IdeHelper\Test\TestCase\Annotator;

use App\Model\Table\FooTable;
use Cake\Console\ConsoleIo;
use Cake\Database\Schema\TableSchema;
use Cake\ORM\TableRegistry;
use IdeHelper\Annotator\AbstractAnnotator;
use IdeHelper\Annotator\ModelAnnotator;
use IdeHelper\Console\Io;
use PHPUnit_Framework_Constraint_IsEqual;
use Tools\TestSuite\ConsoleOutput;
use Tools\TestSuite\TestCase;

/**
 */
class ModelAnnotatorTest extends TestCase {

	/**
	 * @var array
	 */
	public $fixtures = [
		'plugin.ide_helper.foo'
	];

	/**
	 * @var \Tools\TestSuite\ConsoleOutput
	 */
	protected $out;

	/**
	 * @var \Tools\TestSuite\ConsoleOutput
	 */
	protected $err;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->out = new ConsoleOutput();
		$this->err = new ConsoleOutput();
		$consoleIo = new ConsoleIo($this->out, $this->err);
		$this->io = new Io($consoleIo);

		$x = TableRegistry::get('IdeHelper.Foo', ['className' => FooTable::class]);
		$columns = [
			'id' => [
				'type' => 'integer',
				'length' => 11,
				'unsigned' => false,
				'null' => false,
				'default' => null,
				'comment' => '',
				'autoIncrement' => true,
				'baseType' => null,
				'precision' => null
			],
			'name' => [
				'type' => 'string',
				'length' => 100,
				'null' => false,
				'default' => null,
				'comment' => '',
				'baseType' => null,
				'precision' => null,
				'fixed' => null
			],
			'content' => [
				'type' => 'string',
				'length' => 100,
				'null' => false,
				'default' => null,
				'comment' => '',
				'baseType' => null,
				'precision' => null,
				'fixed' => null
			],
			'created' => [
				'type' => 'datetime',
				'length' => null,
				'null' => true,
				'default' => null,
				'comment' => '',
				'baseType' => null,
				'precision' => null
			],
		];
		$schema = new TableSchema('Foo', $columns);
		$x->setSchema($schema);
		TableRegistry::set('Foo', $x);
	}

	/**
	 * @return void
	 */
	public function testAnnotate() {
		$annotator = $this->_getAnnotatorMock([]);

		$expectedContent = str_replace("\r\n", "\n", file_get_contents(TEST_FILES . 'Model/FooTable.php'));
		$callback = function($value) use ($expectedContent) {
			$value = str_replace(["\r\n", "\r"], "\n", $value);
			if ($value !== $expectedContent) {
				$this->debug($expectedContent);
				$this->debug($value);
			}
			return $value === $expectedContent;
		};
		$annotator->expects($this->once())->method('_storeFile')->with($this->anything(), $this->callback($callback));

		$path = APP . 'Model/Table/FooTable.php';
		$annotator->annotate($path);

		$output = (string)$this->out->output();

		$this->assertTextContains('FooTable', $output);
	}

	public function textEqualTo($value, $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false) {
		$value = str_replace(["\r\n", "\r"], "\n", $value);

		return new PHPUnit_Framework_Constraint_IsEqual(
			$value,
			$delta,
			$maxDepth,
			$canonicalize,
			$ignoreCase
		);
	}

	/**
	 * @param array $params
	 * @return \IdeHelper\Annotator\ModelAnnotator|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected function _getAnnotatorMock(array $params) {
		$params += [AbstractAnnotator::CONFIG_DRY_RUN => true];
		return $this->getMockBuilder(ModelAnnotator::class)->setMethods(['_storeFile'])->setConstructorArgs([$this->io, $params])->getMock();
	}

}
