<?php
/**
 * Copyright (c) 2014 Thomas Müller <deepdiver@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
namespace Test\Repair;

/**
 * Tests for the converting of MySQL tables to InnoDB engine
 *
 * @see \OC\Repair\RepairMimeTypes
 */
class RepairInnoDB extends \Test\TestCase {

	/** @var \OC\RepairStep */
	private $repair;

	/** @var \Doctrine\DBAL\Connection */
	private $connection;

	/** @var string */
	private $tableName;

	protected function setUp() {
		parent::setUp();

		$this->connection = \OC_DB::getConnection();
		if (!$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MySqlPlatform) {
			$this->markTestSkipped("Test only relevant on MySql");
		}

		$dbPrefix = \OC::$server->getConfig()->getSystemValue("dbtableprefix");
		$this->tableName = $this->getUniqueID($dbPrefix . "_innodb_test");
		$this->connection->exec("CREATE TABLE $this->tableName(id INT) ENGINE MyISAM");

		$this->repair = new \OC\Repair\InnoDB();
	}

	protected function tearDown() {
		$this->connection->getSchemaManager()->dropTable($this->tableName);
		parent::tearDown();
	}

	public function testInnoDBConvert() {
		$result = $this->countMyIsamTables();
		$this->assertEquals(1, $result);

		$this->repair->run();

		$result = $this->countMyIsamTables();
		$this->assertEquals(0, $result);
	}

	/**
	 * @param $dbName
	 * @return mixed
	 */
	private function countMyIsamTables() {
		$dbName = \OC::$server->getConfig()->getSystemValue("dbname");

		$result = $this->connection->fetchColumn(
			"SELECT count(*) FROM information_schema.tables WHERE table_schema = ? and table_name = ? AND engine = 'MyISAM'",
			array($dbName, $this->tableName)
		);
		return $result;
	}
}
