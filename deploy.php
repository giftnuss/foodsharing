<?php

namespace Deployer;

require 'recipe/common.php';

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
set('writable_dirs', ['tmp', 'cache']);
set('http_user', 'www-data');

// default timeout of 300 was failing sometimes
set('default_timeout', 600);

// Hosts
host('beta')
	->hostname('banana.foodsharing.de')
	->user('deploy')
	->set('deploy_path', '~/beta-deploy');

host('production')
	->hostname('banana.foodsharing.de')
	->user('deploy')
	->set('deploy_path', '~/production-deploy');

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

desc('Deploy your project');
task('deploy', [
	'deploy:info',
	'deploy:prepare',
	'deploy:lock',
	'deploy:release',
	'deploy:update_code',
	'deploy:writable',
	'deploy:shared',
	'deploy:clear_paths',
	'deploy:create_revision',
	'deploy:symlink',
	'deploy:unlock',
	'cleanup',
	'success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
