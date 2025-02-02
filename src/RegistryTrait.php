<?php

declare(strict_types=1);

trait RegistryTrait {

	/**
	 * @var object[]|null
	 */
	private static $members = null;

	/**
     * Registers an object in the registry.
     *
     * @throws \InvalidArgumentException if the member name is already reserved.
     */
	private static function _registryRegister(string $name, object $member) : void {
		if(self::$members === null) {
			throw new \Error("Cannot register members outside of " . self::class . "::setup()");
		}

		if(preg_match('/^(?!\d)[A-Za-z\d_]+$/u', $name) === 0) {
			throw new \InvalidArgumentException("Invalid member name \"$name\", should only contain letters, numbers and underscores, and must not start with a number");
		}
		$upperName = mb_strtoupper($name);

		if(isset(self::$members[$upperName])) {
			throw new \InvalidArgumentException("\"$upperName\" is already reserved");
		}
		self::$members[$upperName] = $member;
	}

	/**
     * Inserts default entries into the registry. 
     * This method should be implemented in the using class.
     */
	abstract protected static function setup() : void;

	/**
     * Ensures the registry is initialized. Initializes if necessary.
     *
     * @throws \InvalidArgumentException if the initialization fails.
     */
	protected static function checkInit() : void {
		if(self::$members === null) {
			self::$members = [];
			self::setup();
		}
	}

	/**
     * Retrieves a member from the registry by its name.
     *
     * @throws \InvalidArgumentException if the member is not found.
     */
	private static function _registryFromString(string $name) : object {
		self::checkInit();

		if(self::$members === null) {
			throw new \Error(self::class . "::checkInit() did not initialize self::\$members correctly");
		}
		$upperName = mb_strtoupper($name);

		if(!isset(self::$members[$upperName])) {
			throw new \InvalidArgumentException("No such registry member: " . self::class . "::" . $upperName);
		}
		return self::preprocessMember(self::$members[$upperName]);
	}

    /**
     * Processes the member before returning it.
     * This can be overridden for custom preprocessing.
     */
	protected static function preprocessMember(object $member) : object {
		return $member;
	}

	/**
     * Magic method for static calls to fetch registry members.
     *
     * @param string $name The name of the member.
     * @param mixed[] $args The arguments passed to the method.
     * 
     * @return object The registry member.
     * 
     * @throws \ArgumentCountError if the number of arguments is incorrect.
     * @throws \Error if the member is not found.
     */
	public static function __callStatic($name, $args) {
		if(count($args) > 0) {
			throw new \ArgumentCountError("Expected exactly 0 arguments, " . count($args) . " passed");
		}

		// Fast path: return member if already initialized.
		if(self::$members !== null && isset(self::$members[$name])) {
			return self::preprocessMember(self::$members[$name]);
		}

		// Fallback: attempt to retrieve member from registry.
		try {
			return self::_registryFromString($name);
		} catch(\InvalidArgumentException $e) {
			throw new \Error($e->getMessage(), 0, $e);
		}
	}

	/**
     * Returns all registry members.
     *
     * @return object[] The list of registry members.
     */
	private static function _registryGetAll() : array {
		self::checkInit();
		return array_map(self::preprocessMember(...), self::$members ?? throw new \Error(self::class . "::checkInit() did not initialize self::\$members correctly"));
	}
}
?>