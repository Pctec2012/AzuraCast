<?php
namespace AzuraCast\Console\Command;

use Entity;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateConfig extends \App\Console\Command\CommandAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('azuracast:config:migrate')
            ->setDescription('Migrate existing configuration to new INI format if any exists.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env_path = APP_INCLUDE_BASE.'/env.ini';
        $settings = [];

        if (file_exists($env_path)) {

            $settings = parse_ini_file($env_path);

            if (!empty($settings['db_password'])) {
                $output->writeln('Configuration already set up.');
                return false;
            }

        }

        if (empty($settings['application_env']) && file_exists(APP_INCLUDE_BASE . '/.env')) {
            $settings['application_env'] = @file_get_contents(APP_INCLUDE_BASE . '/.env');
        }

        if (file_exists(APP_INCLUDE_BASE.'/config/db.conf.php')) {

            $db_conf = include(APP_INCLUDE_BASE.'/config/db.conf.php');
            $settings['db_password'] = $db_conf['password'];

            if ($db_conf['user'] === 'root') {
                $settings['db_username'] = 'root';
            }

        }

        $ini_data = [
            ';',
            '; AzuraCast Environment Settings',
            ';',
            '; This file is automatically generated by AzuraCast.',
            ';',
            '[configuration]',
        ];

        foreach($settings as $setting_key => $setting_val) {
            $ini_data[] = $setting_key . '="' . $setting_val . '"';
        }

        file_put_contents($env_path, implode("\n", $ini_data));

        $output->writeln('Configuration successfully written.');
        return 0;
    }
}