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
set('shared_dirs', ['images', 'data', 'tmp', 'cache/searchindex']);

// Writable dirs by web server
set('writable_dirs', ['tmp']);
set('http_user', 'www-data');
//set('clear_paths', ['cache/.views-cache', 'cache/di-cache.php', 'cache/prod', 'cache/log', 'cache/dev']);

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
task('deploy:create_revision', './scripts/generate-revision.sh');

desc('Build the frontend');
task('deploy:build_frontend', 'cd client && yarn && yarn build');

desc('Deploy your project');
task('deploy', [
	'deploy:info',
	'deploy:prepare',
	'deploy:lock',
	'deploy:release',
	'deploy:update_code',
	'deploy:writable',
	'deploy:shared',
	'deploy:vendors',
	'deploy:clear_paths',
	'deploy:create_revision',
	'deploy:build_frontend',
	'deploy:symlink',
	'deploy:unlock',
	'cleanup',
	'success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
