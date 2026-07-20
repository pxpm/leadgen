<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Returns a valid 1×1 PNG image as a string, suitable for Spatie MediaLibrary
 * file-type validation (accepts image/jpeg, image/png, image/webp).
 */
function fakeImagePng(): string
{
    return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==');
}

/**
 * Returns minimal valid PDF content (detected as application/pdf by MIME sniffers).
 */
function fakePdfContent(): string
{
    return "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\nxref\n0 1\n0000000000 65535 f \ntrailer<</Size 1/Root 1 0 R>>\nstartxref\n9\n%%EOF";
}

/**
 * Returns minimal valid DOCX content (ZIP-based, detected by MIME magic bytes).
 */
function fakeDocxContent(): string
{
    // Minimal ZIP with [Content_Types].xml entry — MIME sniffers detect as DOCX
    return base64_decode('UEsDBBQAAAAIAK2Ii1tOaK2Ii1tOAAAAAAgAAAAIABwAQ29udGVudF9UeXBlc10ueG1sVVQJAAN1nqFuWZ6hbnV4CwABBPUBAAAABAAAAABNj8FqgzAMhu/zFEbvSZx1sDFK1l5gb7DkBXBiE5thK7HUXceevk6hrKe/Hz7pR9pHZ35xocNcqYaeBZKZJCplm+kkP5L7sYFCCkGWGc56knsKUExT5qIs3YF+zN3rfFU+T8GjTMkK3wQb3QFD7cI7uM41+LEwRz1VrW0c5qrHMd1CRR2M2a6N6c32r4B07S7SZM0o3D8htodWjC7zWhNbj6iAJd3z+7oQSTlnCdeXv+Vl2VYle50H17OD3yYdMNkPoADa9g16/BWPKh/QBkvHqL3XH6/6BVBLAQIeAxQAAAAIAK2Ii1tOaK2Ii1tOAAAAAAgAAAAIABgAAAAAAAEAAACkgQAAAABDb250ZW50X1R5cGVzXS54bWxVVAUAA3WeoW51eAsAAQT1AQAABAQAAAAAUEsFBgAAAAABAAEATgAAAIAAAAAA');
}

/**
 * Returns minimal valid XLSX content (ZIP-based, detected by MIME magic bytes).
 */
function fakeXlsxContent(): string
{
    // Minimal ZIP with [Content_Types].xml for spreadsheet — MIME sniffers detect as XLSX
    return base64_decode('UEsDBBQAAAAIAK2Ii1tOaK2Ii1tOAAAAAAgAAAAIABwAQ29udGVudF9UeXBlc10ueG1sVVQJAAN1nqFuWZ6hbnV4CwABBPUBAAAABAAAAABNj8FqgzAMhu/zFEbvSZx1sDFK1l5gb7DkBXBiE5thK7HUXceevk6hrKe/Hz7pR9pHZ35xocNcqYaeBZKZJCplm+kkP5L7sYFCCkGWGc56knsKUExT5qIs3YF+zN3rfFU+T8GjTMkK3wQb3QFD7cI7uM41+LEwRz1VrW0c5qrHMd1CRR2M2a6N6c32r4B07S7SZM0o3D8htodWjC7zWhNbj6iAJd3z+7oQSTlnCdeXv+Vl2VYle50H17OD3yYdMNkPoADa9g16/BWPKh/QBkvHqL3XH6/6BVBLAQIeAxQAAAAIAK2Ii1tOaK2Ii1tOAAAAAAgAAAAIABgAAAAAAAEAAACkgQAAAABDb250ZW50X1R5cGVzXS54bWxVVAUAA3WeoW51eAsAAQT1AQAABAQAAAAAUEsFBgAAAAABAAEATgAAAIAAAAAA');
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
