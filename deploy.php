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

// Custom tasks
task('artisan:seed_critical', function () {
    run('{{bin/php}} {{release_or_current_path}}/artisan db:seed --class=IndustrySeeder --force');
});

task('npm:install', function () {
    run('cd {{release_or_current_path}} && npm ci');
});

task('npm:build', function () {
    run('cd {{release_or_current_path}} && npm run build');
});

task('artisan:generate_sitemap', function () {
    run('{{bin/php}} {{release_or_current_path}}/artisan app:generate-sitemap');
});

task('artisan:queue_restart', function () {
    run('{{bin/php}} {{release_or_current_path}}/artisan queue:restart');
});

// Hook our custom tasks around the recipe's built-in sequence.
// The recipe already runs artisan:migrate, artisan:optimize, and
// artisan:storage:link — we don't re-trigger those, just chain ours.
after('artisan:migrate', 'artisan:seed_critical');
after('deploy:vendors', 'npm:install');
after('npm:install', 'npm:build');
after('deploy:symlink', 'artisan:queue_restart');
after('deploy:symlink', 'artisan:horizon:terminate');
after('deploy:symlink', 'artisan:generate_sitemap');

after('deploy:failed', 'deploy:unlock');
