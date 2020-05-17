<?php

namespace Foodsharing\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class AnalyzeTranslationsCommand extends Command
{
	protected static $defaultName = 'translations:write_as_yaml';

	private $database;
	private $storeGateway;

	public function __construct()
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setDescription('Converts a set of language files to yaml');
	}

	private function processFile(array $data, string $file, OutputInterface $output): array
	{
		global $g_lang;
		$g_lang = [];
		$output->writeln("processing file $file");
		include $file;
		foreach ($g_lang as $k => $v) {
			if (array_key_exists($k, $data)) {
				$output->writeln("($file): Key $k already existing from " . $data[$k]['origin'] . ' existing followed by new:');
				$output->writeln($data[$k]['value']);
				$output->writeln($v);
			} else {
				$data[$k]['value'] = $v;
				$data[$k]['origin'] = $file;
			}
		}
		$g_lang = [];

		return $data;
	}

	protected function execute(InputInterface $input, OutputInterface $output): void
	{
		$map = [];
		$map = $this->processFile($map, 'lang/DE/de.php', $output);
		$handle = opendir('lang/DE');
		while (false !== ($entry = readdir($handle))) {
			if ($entry != '.' && $entry != '..' && $entry != 'de.php') {
				$map = $this->processFile($map, 'lang/DE/' . $entry, $output);
			}
		}
		closedir($handle);
		$g_lang = [];
		include 'lang/DE/de.php';
		$yaml = Yaml::dump($g_lang);
		$output->write($yaml);
	}
}
