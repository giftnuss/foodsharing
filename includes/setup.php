<?php

require __DIR__ . '/../vendor/autoload.php';

// Move this into a better place!
$pdo = new PDO('mysql:host=db;dbname=foodsharing', 'root', 'root', []);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

$container = new ContainerBuilder();

$loader = new YamlFileLoader($container, new FileLocator(__DIR__));
$loader->load('services.yaml');

$container->compile();
