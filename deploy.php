<?php

namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'foodsharing');

// Project repository
set('repository', 'git@gitlab.com:foodsharing-dev/foodsharing.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
set('shared_files', ['config.inc.prod.php']);
set('shared_dirs', ['images', 'data', 'tmp']);

// Writable dirs by web server
set('writable_dirs', ['tmp']);
set('http_user', 'www-data');
set('clear_paths', ['tmp/.views-cache', 'tmp/di-cache.php']);

// Hosts

host('beta')
	->hostname('banana.foodsharing.de')
	->user('deploy')
	//->set('deploy_path', '/var/www/lmr-beta/www');
	->set('deploy_path', '~/beta-deploy');

host('production')
	->hostname('banana.foodsharing.de')
	->user('deploy')
	//->set('deploy_path', '/var/www/lmr-beta/www');
	->set('deploy_path', '~/production-deploy');

desc('Create the revision information');
task('deploy:create_revision', '
./scripts/generate-revision.sh
');
// Tasks

desc('Deploy your project');
task('deploy', [
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
