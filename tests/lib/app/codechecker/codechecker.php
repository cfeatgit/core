<?php
/**
 * Copyright (c) 2015 Thomas Müller <deepdiver@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\App\CodeChecker;

use OC;
use Test\TestCase;
use OC\App\CodeChecker\Error;

class CodeChecker extends TestCase {

	/**
	 * @dataProvider providesFilesToCheck
	 * @param $expectedErrorToken
	 * @param $expectedErrorCode
	 * @param $fileToVerify
	 */
	public function testFindInvalidUsage($expectedErrorToken, $expectedErrorCode, $fileToVerify) {
		$checker = new OC\App\CodeChecker\CodeChecker();
		/** @var Error[] $errors */
		$errors = $checker->analyseFile(OC::$SERVERROOT . "/tests/data/app/code-checker/$fileToVerify");

		$this->assertEquals(1, count($errors));
		$this->assertEquals($expectedErrorCode, $errors[0]->getCode());
		$this->assertEquals($expectedErrorToken, $errors[0]->getDisallowedToken());
	}

	public function providesFilesToCheck() {
		return [
			['OC_Hook', 1000, 'test-extends.php'],
			['oC_Avatar', 1001, 'test-implements.php'],
			['OC_App', 1002, 'test-static-call.php'],
			['OC_API', 1003, 'test-const.php'],
			['OC_AppConfig', 1004, 'test-new.php'],
			['==', 1005, 'test-equal.php'],
			['!=', 1005, 'test-not-equal.php'],
		];
	}

	/**
	 * @dataProvider validFilesData
	 * @param $fileToVerify
	 */
	public function testPassValidUsage($fileToVerify) {
		$checker = new OC\App\CodeChecker\CodeChecker();
		$errors = $checker->analyseFile(OC::$SERVERROOT . "/tests/data/app/code-checker/$fileToVerify");

		$this->assertEquals(0, count($errors));
	}

	public function validFilesData() {
		return [
			['test-identical-operator.php'],
		];
	}
}
