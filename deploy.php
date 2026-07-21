<?php

namespace Deployer;

require 'recipe/laravel.php';

set('application', 'liaweb');

set(
    'repository',
    'git@github.com:pxpm/leadgen.git'
);

set('ssh_multiplexing', false);
set('forward_agent', false);

set('keep_releases', 5);

add('shared_files', [
    '.env',
]);

add('shared_dirs', [
    'storage',
]);

add('writable_dirs', [
    'storage',
    'bootstrap/cache',
]);

host('production')
    ->setHostname('liaweb-vps')
    ->setRemoteUser('deploy')
    ->set('deploy_path', '/var/www/app-production')
    ->set('branch', 'main');

host('staging')
    ->setHostname('liaweb-vps')
    ->setRemoteUser('deploy')
    ->set('deploy_path', '/var/www/app-staging')
    ->set('branch', 'develop');

task('artisan:optimize', function () {
    run('{{bin/php}} {{release_or_current_path}}/artisan optimize');
});

task('artisan:migrate_force', function () {
    run('{{bin/php}} {{release_or_current_path}}/artisan migrate --force');
});

task('artisan:seed_critical', function () {
    run('{{bin/php}} {{release_or_current_path}}/artisan db:seed --class=IndustrySeeder --no-interaction');
});

task('artisan:queue_restart', function () {
    run('{{bin/php}} {{release_or_current_path}}/artisan queue:restart');
});

task('npm:install', function () {
    run('cd {{release_or_current_path}} && npm ci');
});

task('npm:build', function () {
    run('cd {{release_or_current_path}} && npm run build');
});

after('deploy:vendors', 'artisan:migrate_force');
after('artisan:migrate_force', 'artisan:seed_critical');

after('deploy:vendors', 'artisan:optimize');

after('deploy:vendors', 'npm:install');
after('npm:install', 'npm:build');

after('deploy:symlink', 'artisan:queue_restart');

after('deploy:failed', 'deploy:unlock');