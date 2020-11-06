<?php

namespace Deployer;

require 'recipe/common.php';
require 'contrib/cachetool.php';

// Project name
set('application', 'foodsharing');

// Project repository
set('repository', 'git@gitlab.com:foodsharing-dev/foodsharing.git');

// [Optional] Allocate tty for git clone. Default value is false.
// Needs to be false when we run in CI environment
set('git_tty', false);

// Shared files/dirs between deploys
set('shared_files', ['config.inc.prod.php']);
set('shared_dirs', ['images', 'data', 'tmp']);

// Writable dirs by web server
set('writable_dirs', ['tmp', 'cache', 'var']);
set('http_user', 'www-data');

// default timeout of 300 was failing sometimes
set('default_timeout', 600);

// Hosts
host('beta')
	->setHostname('dragonfruit.foodsharing.network')
	->setRemoteUser('deploy')
	->set('deploy_path', '/var/www/beta')
	->set('cachetool', '/var/run/php7-fpm-beta.sock');

host('production')
	->setHostname('dragonfruit.foodsharing.network')
	->setRemoteUser('deploy')
	->set('deploy_path', '/var/www/production')
	->set('cachetool', '/var/run/php7-fpm-production.sock');

// Tasks
desc('Create the revision information');
task('deploy:create_revision', function () {
	$revision = input()->getOption('revision');
	cd('{{release_path}}');
	run("./scripts/generate-revision.sh $revision");
});

task('deploy:update_code', function () {
	upload(__DIR__ . '/', '{{release_path}}', [
		'--exclude=.git',
		'--exclude=client',
		'--exclude=migrations',
		'--exclude=deployer',
		'--compress-level=9'
	]);
});

task('deploy:cache:warmup', function () {
	run('FS_ENV=prod {{release_path}}/bin/console cache:warmup -e prod');
})->desc('Warmup symfony cache');

desc('Deploy your project');
task('deploy', [
	'deploy:info',
	'deploy:setup',
	'deploy:lock',
	'deploy:release',
	'deploy:update_code',
	'deploy:writable',
	'deploy:shared',
	'deploy:clear_paths',
	'deploy:create_revision',
	'deploy:cache:warmup',
	'deploy:symlink',
	'deploy:unlock',
	'deploy:cleanup',
	'deploy:success'
]);
after('deploy:symlink', 'cachetool:clear:opcache');

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
