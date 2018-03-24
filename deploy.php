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
set('writable_dirs', ['tmp', 'cache/searchindex']);
set('http_user', 'www-data');
set('clear_paths', ['tmp/.views-cache', 'tmp/di-cache.php']);

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

task('build', 'cd client && yarn && yarn build');

desc('Create the revision information');
task('deploy:create_revision', './scripts/generate-revision.sh');

desc('Deploy your project');
task('deploy', [
	'build',
	'deploy:info',
	'deploy:prepare',
	'deploy:lock',
	'deploy:release',
	'deploy:update_code',
	'deploy:shared',
	'deploy:writable',
	'deploy:vendors',
	'deploy:clear_paths',
	'deploy:create_revision',
	'deploy:symlink',
	'deploy:unlock',
	'cleanup',
	'success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
