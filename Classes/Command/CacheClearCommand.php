<?php
namespace SES\Console\Command;

/***************************************************************
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 ***************************************************************/

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CacheClearCommand for clearing the cache.
 * @package SES\Console\Command
 * @author Sebastian Seidelmann <sebastian.seidelmann@wfp2.com>, wfp:2 GmbH & Co. KG
 */
class CacheClearCommand extends Command {

    protected function configure()
    {
        $this->setName("wfp2:cache:clear")
            ->setDescription("Clears the cache instances")
            ->addArgument('all')
            ->setHelp(<<<EOT
Clears the cache instances

Usage:

<info>php app/console wfp2:cache:clear</info>
EOT
            );
    }

    /**
     * Executes the cache clear.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Clear the cache for ' . \WFP2Environment::getWebPath(true));
    }
}