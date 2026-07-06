<?php

use Illuminate\Support\Facades\{Config, Http};
use TomShaw\Dropbox\{Dropbox, DropboxClient};
use TomShaw\Dropbox\Enums\WriteMode;

beforeEach(function () {
    Config::set('dropbox', require realpath(__DIR__.DIRECTORY_SEPARATOR.'Mock'.DIRECTORY_SEPARATOR.'config.php'));

    app(DropboxClient::class)->setAccessToken(['access_token' => 'test-token']);
});

it('lists a folder with the bearer token and json body', function () {
    Http::fake(['api.dropboxapi.com/2/files/list_folder' => Http::response(['entries' => []])]);

    $result = Dropbox::files()->listFolder('/photos', recursive: true, limit: 50);

    expect($result)->toBe(['entries' => []]);

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return $request->url() === 'https://api.dropboxapi.com/2/files/list_folder'
            && $request->header('Authorization')[0] === 'Bearer test-token'
            && $request->header('Content-Type')[0] === 'application/json'
            && $body === [
                'path' => '/photos',
                'recursive' => true,
                'include_deleted' => false,
                'include_media_info' => false,
                'limit' => 50,
            ];
    });
});

it('creates folders via the v2 endpoint', function () {
    Http::fake(['api.dropboxapi.com/2/files/create_folder_v2' => Http::response(['metadata' => ['name' => 'docs']])]);

    Dropbox::files()->createFolder('/docs');

    Http::assertSent(fn ($request) => $request->url() === 'https://api.dropboxapi.com/2/files/create_folder_v2'
        && json_decode($request->body(), true) === ['path' => '/docs', 'autorename' => false]);
});

it('structures search options per the search_v2 schema', function () {
    Http::fake(['api.dropboxapi.com/2/files/search_v2' => Http::response(['matches' => []])]);

    Dropbox::files()->search('report', '/work', maxResults: 25, includeHighlights: true);

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return $body['query'] === 'report'
            && $body['options'] === ['max_results' => 25, 'filename_only' => false, 'path' => '/work']
            && $body['match_field_options'] === ['include_highlights' => true];
    });
});

it('omits the search path option when searching the whole dropbox', function () {
    Http::fake(['api.dropboxapi.com/2/files/search_v2' => Http::response(['matches' => []])]);

    Dropbox::files()->search('report');

    Http::assertSent(fn ($request) => ! array_key_exists('path', json_decode($request->body(), true)['options']));
});

it('deletes multiple paths in a batch', function () {
    Http::fake(['api.dropboxapi.com/2/files/delete_batch' => Http::response(['.tag' => 'async_job_id', 'async_job_id' => 'job'])]);

    Dropbox::files()->deleteBatch(['/a.txt', '/b.txt']);

    Http::assertSent(fn ($request) => json_decode($request->body(), true) === [
        'entries' => [['path' => '/a.txt'], ['path' => '/b.txt']],
    ]);
});

it('fetches thumbnails from the content host', function () {
    Http::fake(['content.dropboxapi.com/2/files/get_thumbnail_v2' => Http::response('binary-image-data', 200, ['Content-Type' => 'image/jpeg'])]);

    $thumbnail = Dropbox::files()->getThumbnail('/photo.png', size: 'w256h256');

    expect($thumbnail)->toBe('binary-image-data');

    Http::assertSent(function ($request) {
        $arguments = json_decode($request->header('Dropbox-API-Arg')[0], true);

        return $request->url() === 'https://content.dropboxapi.com/2/files/get_thumbnail_v2'
            && $arguments['resource'] === ['.tag' => 'path', 'path' => '/photo.png']
            && $arguments['size'] === 'w256h256';
    });
});

it('downloads file contents from the content host', function () {
    Http::fake(['content.dropboxapi.com/2/files/download' => Http::response('file-contents')]);

    expect(Dropbox::files()->download('/readme.md'))->toBe('file-contents');

    Http::assertSent(fn ($request) => json_decode($request->header('Dropbox-API-Arg')[0], true) === ['path' => '/readme.md']);
});

it('returns metadata from the api result header when streaming downloads', function () {
    Http::fake([
        'content.dropboxapi.com/2/files/download' => Http::response('file-contents', 200, [
            'Dropbox-API-Result' => json_encode(['name' => 'readme.md', 'size' => 13]),
        ]),
    ]);

    $metadata = Dropbox::files()->downloadTo('/readme.md', sys_get_temp_dir().'/dropbox-test-download.md');

    expect($metadata)->toBe(['name' => 'readme.md', 'size' => 13]);
});

it('uploads small files in a single request', function () {
    Http::fake(['content.dropboxapi.com/2/files/upload' => Http::response(['name' => 'notes.txt'])]);

    $source = tempnam(sys_get_temp_dir(), 'dbx');
    file_put_contents($source, 'hello dropbox');

    $result = Dropbox::files()->upload('/notes.txt', $source, WriteMode::Overwrite);

    expect($result)->toBe(['name' => 'notes.txt']);

    Http::assertSent(function ($request) {
        $arguments = json_decode($request->header('Dropbox-API-Arg')[0], true);

        return $request->url() === 'https://content.dropboxapi.com/2/files/upload'
            && $request->header('Content-Type')[0] === 'application/octet-stream'
            && $arguments['path'] === '/notes.txt'
            && $arguments['mode'] === 'overwrite';
    });

    unlink($source);
});

it('throws when uploading a missing file', function () {
    Dropbox::files()->upload('/missing.txt', '/nonexistent/missing.txt');
})->throws(InvalidArgumentException::class);

it('uploads large files through chunked upload sessions', function () {
    Http::fake([
        'content.dropboxapi.com/2/upload_session/start' => Http::response(['session_id' => 'session-1']),
        'content.dropboxapi.com/2/upload_session/append_v2' => Http::response(null, 200),
        'content.dropboxapi.com/2/upload_session/finish' => Http::response(['name' => 'big.bin']),
    ]);

    $source = tempnam(sys_get_temp_dir(), 'dbx');
    file_put_contents($source, str_repeat('a', 10).str_repeat('b', 10).str_repeat('c', 5));

    $result = Dropbox::files()->uploadSession('/big.bin', $source, chunkSize: 10);

    expect($result)->toBe(['name' => 'big.bin']);

    Http::assertSent(fn ($request) => $request->url() === 'https://content.dropboxapi.com/2/upload_session/start'
        && $request->body() === str_repeat('a', 10));

    $appendOffsets = [];

    Http::assertSent(function ($request) use (&$appendOffsets) {
        if ($request->url() !== 'https://content.dropboxapi.com/2/upload_session/append_v2') {
            return false;
        }

        $arguments = json_decode($request->header('Dropbox-API-Arg')[0], true);
        $appendOffsets[] = $arguments['cursor']['offset'];

        return $arguments['cursor']['session_id'] === 'session-1';
    });

    expect($appendOffsets)->toBe([10, 20]);

    Http::assertSent(function ($request) {
        if ($request->url() !== 'https://content.dropboxapi.com/2/upload_session/finish') {
            return false;
        }

        $arguments = json_decode($request->header('Dropbox-API-Arg')[0], true);

        return $arguments['cursor'] === ['session_id' => 'session-1', 'offset' => 25]
            && $arguments['commit']['path'] === '/big.bin';
    });

    unlink($source);
});
