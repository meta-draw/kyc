<?php

use MetaDraw\Kyc\KycServiceProvider;
use Orchestra\Testbench\TestCase;

uses(TestCase::class)->in(__DIR__);

function getPackageProviders($app)
{
    return [
        KycServiceProvider::class,
    ];
}

function getEnvironmentSetUp($app)
{
    // Setup default database to use sqlite :memory:
    $app['config']->set('database.default', 'testing');
    $app['config']->set('database.connections.testing', [
        'driver'   => 'sqlite',
        'database' => ':memory:',
        'prefix'   => '',
    ]);
    
    // Load migrations
    $migrationPath = __DIR__ . '/../database/migrations';
    $app['migrator']->path($migrationPath);
}

// Add test helpers
function createKycVerification(array $attributes = [])
{
    return \MetaDraw\Kyc\Models\KycVerification::create(array_merge([
        'user_id' => 1,
        'nationality' => 'US',
        'resident_country' => 'US',
        'dob' => '1990-01-01',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'document_type' => 'passport',
        'country_of_issue' => 'US',
        'document_number' => '123456789',
        'document_issue_date' => '2020-01-01',
        'document_expiry_date' => '2030-01-01',
        'status' => \MetaDraw\Kyc\Enums\KycStatus::Pending,
    ], $attributes));
}