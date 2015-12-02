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

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateExtensionCommand for create some skeletons.
 * @package SES\Console\Command
 * @author Sebastian Seidelmann <sebastian.seidelmann@wfp2.com>, wfp:2 GmbH & Co. KG
 */
class GenerateExtensionCommand extends Generator
{

    protected function configure()
    {
        $this->setName("wfp2:generate:extension")
            ->setDescription("Creates a skeleton for extensions")
            ->setHelp(<<<EOT
Creates a skeleton for a simple extension

Usage:

<info>php app/console wfp2:generate:extension <env></info>
EOT
            );

        $this->setGeneratorName('extension');
    }

    /**
     * Executes the cache clear.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->writeSection($output, 'Extension generation');

        /* @var $dialog \Symfony\Component\Console\Helper\DialogHelper */
        $dialog = $this->getHelper('dialog');

        $path = $this->getExtensionPath($dialog, $output);

        if (!mkdir($path)) {
            throw new \RuntimeException('Path ' . $path . ' could not created');
        }

        $output->writeln(sprintf('Generate a new extension skeleton into <info>%s</info>', $path));


        $errors = array();
        $runner = $this->getRunner($output, $errors);

        $parameter = array(
            'name' => basename($path)
        );

        $path = realpath($path) . DIRECTORY_SEPARATOR;

        // Generate the folders
        $runner($this->generateFolders($output, $path));

        // Generate the files
        $runner($this->generateFiles($output, $path, $parameter));




        $this->writeGeneratorSummery($output, $errors);
    }

    /**
     * Generates the folders for a extension.
     * @param OutputInterface $output
     * @param string          $path
     * @return array
     */
    private function generateFolders(OutputInterface $output, $path)
    {
        $errors = array();
        $output->writeln('> Generate the folders');

        $folders = array(
            'Resources',
            'Resources/Public',
            'Resources/Private',
            'Classes',
            'Classes/Controller',
            'Classes/Domain',
            'Configuration'
        );

        foreach ($folders as $folder) {
            if (!mkdir($path . $folder)) {
                $errors[] = 'Folder could not created';
            }
        }

        return $errors;
    }

    /**
     * Generates the files for a extension.
     * @param OutputInterface $output
     * @param string          $path
     * @param array           $parameter
     * @return array
     */
    private function generateFiles(OutputInterface $output, $path, array $parameter = array())
    {
        $errors = array();
        $output->writeln('> Generate the files');


        $files = array(
            'ext_emconf.php',
            'ext_localconf.php',
            'ext_tables.php',
            'Configuration/setup.txt',
            'Configuration/constants.txt'
        );
        foreach ($files as $file) {
            try {
                $this->renderFile($file, $path . $file, $parameter);
            } catch (\Exception $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        return $errors;
    }

    /**
     * Gets the extension path.
     * @param DialogHelper    $dialogHelper
     * @param OutputInterface $output
     * @return string
     */
    private function getExtensionPath(DialogHelper $dialogHelper, OutputInterface $output)
    {
        return $dialogHelper->askAndValidate(
            $output,
            $this->createQuestion('Enter your package name'),
            function ($answer) {
                if (is_dir(\WFP2Environment::getExtPath() . $answer)) {
                    throw new \RuntimeException('Extension ' . $answer . ' already exists');
                }

                return \WFP2Environment::getExtPath() . $answer;
            }
        );
    }
}