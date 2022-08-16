<?php
namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'BotQio');

// Project repository
set('repository', 'https://github.com/BotQio/BotQio.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server 
set('writable_mode', 'chmod');
set('writable_chmod_mode', '0775');
set('writable_recursive', false);
set('writable_chmod_recursive', false);
add('writable_dirs', [
    'storage/framework/cache/data',
    // 'storage/logs/laravel.log',   TODO Writable file?
]);
set('allow_anonymous_stats', false);

// Hosts
import('hosts.yml');
    
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

after('deploy:symlink', 'deploy:botqio');

desc('First time setup');
task('deploy:first_time_setup', function () {
    artisan('key:generate --force')();
    artisan('passport:keys')();
    artisan('passport:client --personal --no-interaction --name="BotQio Personal Client"')();
});

desc('Clear deployed caching');
task('deploy:cache_bust', [
    'artisan:cache:clear',
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:view:cache',
]);

desc('Install npm libraries');
task('deploy:npm:install', function () {
    run('cd {{release_path}} && npm install');
});

desc('Build prod artifacts');
task('deploy:npm:run', function () {
    run('cd {{release_path}} && npm run prod');
});

after('deploy:vendors', 'deploy:npm:install');
after('deploy:npm:install', 'deploy:npm:run');

desc('Deploy BotQio');
task('deploy:botqio', function () {
    $envless = get('envless', false);
    if($envless) {
        writeln('Envless!');
        // We have no .env file setup at this point
        return;
    }

    // This is the normal flow
    artisan('migrate --force')();
    artisan('horizon:terminate')();
    artisan('websockets:restart')();

    invoke('deploy:cache_bust');
});
