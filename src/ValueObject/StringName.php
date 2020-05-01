<?php

namespace IdeHelper\ValueObject;

/**
 * Holds a string with auto added `'` chars - can be auto-casted to string on output.
 */
class StringName implements ValueObjectInterface {

	/**
	 * @var string
	 */
	protected $value;

	/**
	 * @param string $value
	 */
	private function __construct($value) {
		$this->value = $value;
	}

	/**
	 * Creates itself from a string.
	 *
	 * @param string $value
	 *
	 * @return static
	 */
	public static function create($value) {
		return new static($value);
	}

	/**
	 * @return string
	 */
	public function raw() {
		return $this->value;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return '\'' . $this->value . '\'';
	}

}
