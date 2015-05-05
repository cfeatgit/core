<?php
/**
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OC\Core\Command\App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use OC\App\CodeChecker\Error;

class CheckCode extends Command {
	protected function configure() {
		$this
			->setName('app:check-code')
			->setDescription('check code to be compliant')
			->addArgument(
				'app-id',
				InputArgument::REQUIRED,
				'check the specified app'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$appId = $input->getArgument('app-id');
		$codeChecker = new \OC\App\CodeChecker\CodeChecker();
		$codeChecker->listen('CodeChecker', 'analyseFileBegin', function($params) use ($output) {
			$output->writeln("<info>Analysing {$params}</info>");
		});
		$codeChecker->listen('CodeChecker', 'analyseFileFinished', function($params) use ($output) {
			/** @var Error[] $params */

			$count = count($params);
			$output->writeln(" {$count} errors");
			// FIXME: Make work with error class
			/*	usort($params, function($a, $b) {
				return $a['line'] >$b['line'];
			});*/

			foreach($params as $p) {
				if($p instanceof Error) {
					$line = sprintf("%' 4d", $p->getLine());
					$output->writeln("    <error>line $line: {$p->getDisallowedToken()} - {$p->getMessage()}</error>");
				} else {
					$line = sprintf("%' 4d", $p['line']);
					$output->writeln("    <error>line $line: {$p['disallowedToken']} - {$p['reason']}</error>");
				}
			}
		});
		$errors = $codeChecker->analyse($appId);
		if (empty($errors)) {
			$output->writeln('<info>App is compliant - awesome job!</info>');
		} else {
			$output->writeln('<error>App is not compliant</error>');
		}
	}
}
