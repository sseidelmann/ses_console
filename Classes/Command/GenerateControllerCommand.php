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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class GenerateControllerCommand for generate controller.
 * @package SES\Console\Command
 * @author Sebastian Seidelmann <sebastian.seidelmann@wfp2.com>, wfp:2 GmbH & Co. KG
 */
class GenerateControllerCommand extends Generator
{

    protected function configure()
    {
        $this->setName("wfp2:generate:controller")
            ->setDescription("Creates a skeleton for extensions")
            ->addArgument('controller_name', InputArgument::REQUIRED, 'The Controller name')
            ->addOption('extension', null, InputOption::VALUE_REQUIRED, 'The extension name in typo3conf/ext')
            ->setHelp(<<<EOT
Creates a controller for given extension

Usage:

<info>php app/console wfp2:generate:controller --extension=<extension></info>
EOT
            );

        $this->setGeneratorName('controller');
    }

    /**
     * Executes the cache clear.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->writeSection($output, 'Controller generation');

        $extensionName  = $input->getOption('extension');
        $controllerName = $input->getArgument('controller_name');

        if (!$extensionName) {
            throw new \RuntimeException('Parameter extension is required');
        }

        $path = \WFP2Environment::getExtPath() . $extensionName . DIRECTORY_SEPARATOR;

        if (!is_dir($path)) {
            throw new \RuntimeException('Path ' . $path . ' does not exists');
        }


        $classesPath = $path . 'Classes' . DIRECTORY_SEPARATOR;
        if (!is_dir($classesPath)) {
            mkdir($classesPath);
        }

        $controllerPath = $classesPath . 'Controller' . DIRECTORY_SEPARATOR;
        if (!is_dir($controllerPath)) {
            mkdir($controllerPath);
        }

        if (strpos($controllerName, 'Controller') === false) {
            $controllerName .= 'Controller';
        }

        $output->writeln('Create new controller ' . $controllerName);


        /* @var $dialog \Symfony\Component\Console\Helper\DialogHelper */
        $dialog        = $this->getHelper('dialog');
        $actionContent = array();

        while ($dialog->askConfirmation($output, 'generate action? [y/n]')) {
            $actionNameRaw = $actionName = $dialog->ask($output, 'Please insert your action name: ');

            if (strpos($actionName, 'Action') === false) {
                $actionName .= 'Action';
            }

            $templatePath = $this->mkdir($path . 'Resources/Private/Templates/' . $input->getArgument('controller_name'));
            $templateName = ucfirst(strtolower($actionNameRaw)) . '.html';

            $this->renderFile('view.html', $templatePath . $templateName, array(
                'controller' => $controllerName,
                'action'     => $actionName
            ));

            $actionContent[] = $this->render('action.php', array(
                'actionname' => $actionName,
                'view'       => str_replace(\WFP2Environment::getExtPath(), '', $templatePath . $templateName)
            ));
        };

        $this->renderFile('controller.php', $controllerPath . $controllerName . '.php', array(
            'name'      => $controllerName,
            'namespace' => $this->generateNamespaceForExtensionName($extensionName) . '\\Controller',
            'body'      => implode(PHP_EOL, $actionContent)
        ));

        $errors = array();


        $this->writeGeneratorSummery($output, $errors);
    }
}