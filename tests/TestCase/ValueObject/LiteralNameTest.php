<?php

namespace IdeHelper\Test\TestCase\ValueObject;

use Cake\TestSuite\TestCase;
use IdeHelper\ValueObject\LiteralName;

class LiteralNameTest extends TestCase {

	/**
	 * @return void
	 */
	public function testCreate() {
		$stringName = 'Foo.Baz';
		$result = LiteralName::create($stringName);
		$this->assertSame($stringName, $result->raw());
	}

	/**
	 * @return void
	 */
	public function testToString() {
		$stringName = 'Foo.Baz';
		$object = LiteralName::create($stringName);

		$result = (string)$object;
		$this->assertSame($stringName, $result);
	}

}
