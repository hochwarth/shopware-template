<?php
declare(strict_types=1);

namespace Shopware\Production\HochwarthTools\Command;

use Doctrine\DBAL\Connection;
use Ifsnop\Mysqldump\Mysqldump;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class CreateBundleCommand extends Command
{
    protected static $defaultName = 'hochwarth:create:bundle';
    private string $projectDir;

    private string $bundleTemplate = <<<EOL
<?php declare(strict_types=1);

namespace #namespace#;

use Shopware\Core\Framework\Bundle;#use#

class #class# extends Bundle#theme#
{
    protected \$name = '#class#';
}
EOL;

    private string $servicesXmlTemplate = <<<EOL
<?xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>

    </services>
</container>
EOL;
    private Connection $connection;

    public function __construct(string $projectDir)
    {
        parent::__construct();
        $this->projectDir = $projectDir;
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a Shopware bundle')
            ->addArgument('bundleName', InputArgument::REQUIRED, 'Bundle name')
            ->addArgument('namespace', InputArgument::REQUIRED, 'Bundle namespace')
            ->addOption('theme', 't', InputOption::VALUE_NONE, 'Is theme');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new ShopwareStyle($input, $output);

        $name = ucfirst($input->getArgument('bundleName'));
        $snakeCaseName = (new CamelCaseToSnakeCaseNameConverter())->normalize($name);
        $snakeCaseName = str_replace('_', '-', $snakeCaseName);
        $isTheme = $input->getOption('theme');

        $directory = $this->projectDir . '/src/' . $name;

        try {
            $this->createDirectory($directory . '/Resources/config');
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());

            return self::FAILURE;
        }

        $bundleFile = $directory . '/' . $name . 'Bundle.php';
        $servicesXmlFile = $directory . '/Resources/config/services.xml';

        $namespace = $input->getArgument('namespace');
        $bundle = str_replace(
            ['#namespace#', '#class#', '#theme#', '#use#'],
            [$namespace, $name . 'Bundle', $isTheme ? ' implements ThemeInterface' : '', "\nuse Shopware\\Storefront\\Framework\\ThemeInterface;"],
            $this->bundleTemplate
        );

        file_put_contents($bundleFile, $bundle);
        file_put_contents($servicesXmlFile, $this->servicesXmlTemplate);

        if ($isTheme) {
            try {
                $this->createDirectory($directory . '/Resources/app/');
                $this->createDirectory($directory . '/Resources/app/storefront/');
                $this->createDirectory($directory . '/Resources/app/storefront/src/');
                $this->createDirectory($directory . '/Resources/app/storefront/src/scss');
                $this->createDirectory($directory . '/Resources/app/storefront/src/assets');
                $this->createDirectory($directory . '/Resources/app/storefront/dist');
                $this->createDirectory($directory . '/Resources/app/storefront/dist/storefront');
                $this->createDirectory($directory . '/Resources/app/storefront/dist/storefront/js');
            } catch (\RuntimeException $e) {
                $io->error($e->getMessage());

                return self::FAILURE;
            }

            $themeConfigFile = $directory . '/Resources/theme.json';
            $variableOverridesFile = $directory . '/Resources/app/storefront/src/scss/overrides.scss';

            $themeConfig = str_replace(
                ['#name#', '#snake-case#'],
                [$name, $snakeCaseName],
                $this->getThemeConfigTemplate()
            );


            file_put_contents($themeConfigFile, $themeConfig);
            file_put_contents($variableOverridesFile, $this->getVariableOverridesTemplate());

            touch($directory . '/Resources/app/storefront/src/assets/.gitkeep');
            touch($directory . '/Resources/app/storefront/src/scss/base.scss');
            touch($directory . '/Resources/app/storefront/src/main.js');
            touch($directory . '/Resources/app/storefront/dist/storefront/js/' . $snakeCaseName . '.js');
        }

        $this->registerBundle($name, $namespace);

        return self::SUCCESS;
    }

    private function createDirectory(string $pathName): void
    {
        if (!mkdir($pathName, 0755, true) && !is_dir($pathName)) {
            throw new \RuntimeException(sprintf('Unable to create directory "%s". Please check permissions', $pathName));
        }
    }

    private function getThemeConfigTemplate(): string
    {
        return <<<EOL
{
  "name": "#name#",
  "author": "Hochwarth IT GmbH",
  "views": [
     "@Storefront",
     "@Plugins",
     "@#name#"
  ],
  "style": [
    "app/storefront/src/scss/overrides.scss",
    "@Storefront",
    "app/storefront/src/scss/base.scss"
  ],
  "script": [
    "@Storefront",
    "app/storefront/dist/storefront/js/#snake-case#.js"
  ],
  "asset": [
    "@Storefront",
    "app/storefront/src/assets"
  ]
}
EOL;
    }

    private function getVariableOverridesTemplate(): string
    {
        return <<<EOL
/*
Override variable defaults
==================================================
This file is used to override default SCSS variables from the Shopware Storefront or Bootstrap.

Because of the !default flags, theme variable overrides have to be declared beforehand.
https://getbootstrap.com/docs/4.0/getting-started/theming/#variable-defaults
*/
EOL;
    }

    private function registerBundle(string $name, string $namespace): void
    {
        $configFile = $this->projectDir . '/config/bundles.php';
        $registered = $this->loadRegisteredBundles($configFile);
        $registered[$namespace . '\\' . $name . 'Bundle']['all'] = true;

        $contents = "<?php  declare(strict_types=1);\n\nreturn [\n";
        foreach ($registered as $class => $envs) {
            $contents .= "    $class::class => [";
            foreach ($envs as $env => $value) {
                $booleanValue = var_export($value, true);
                $contents .= "'$env' => $booleanValue, ";
            }
            $contents = substr($contents, 0, -2)."],\n";
        }
        $contents .= "];\n";

        if (!is_dir(\dirname($configFile))) {
            mkdir(\dirname($configFile), 0777, true);
        }

        file_put_contents($configFile, $contents);

        if (\function_exists('opcache_invalidate')) {
            opcache_invalidate($configFile);
        }
    }

    private function loadRegisteredBundles(string $file): array
    {
        $bundles = file_exists($file) ? (require $file) : [];
        if (!\is_array($bundles)) {
            $bundles = [];
        }

        return $bundles;
    }
}
