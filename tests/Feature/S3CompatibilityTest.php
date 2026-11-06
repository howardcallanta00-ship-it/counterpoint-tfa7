<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Aws\MockHandler;
use Aws\Result;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class S3CompatibilityTest extends TestCase
{
    public function test_public_s3_disk_uses_relative_keys_no_acl_and_public_url(): void
    {
        $mock = new MockHandler([new Result([]), new Result([]), new Result([])]);
        config(['filesystems.disks.public' => [
            'driver' => 's3',
            'key' => 'test-access',
            'secret' => 'test-secret',
            'region' => 'ap-southeast-1',
            'bucket' => 'avatars',
            'endpoint' => 'https://project.storage.supabase.co/storage/v1/s3',
            'url' => 'https://project.supabase.co/storage/v1/object/public/avatars',
            'use_path_style_endpoint' => true,
            'request_checksum_calculation' => 'when_required',
            'response_checksum_validation' => 'when_required',
            'throw' => true,
        ]]);
        Storage::forgetDisk('public');
        (new AppServiceProvider($this->app))->boot();
        $disk = Storage::disk('public');
        $disk->getClient()->getHandlerList()->setHandler($mock);
        $this->assertTrue($disk->put('avatars/unique.jpg', 'image-bytes', ['ContentType' => 'image/jpeg']));
        $request = $mock->getLastRequest();
        $this->assertFalse($request->hasHeader('x-amz-acl'));
        $this->assertFalse($request->hasHeader('x-amz-checksum-crc32'));
        $this->assertSame('/storage/v1/s3/avatars/avatars/unique.jpg', $request->getUri()->getPath());
        $this->assertSame('image/jpeg', $request->getHeaderLine('Content-Type'));
        $this->assertSame('https://project.supabase.co/storage/v1/object/public/avatars/avatars/unique.jpg', $disk->url('avatars/unique.jpg'));
        $this->assertTrue($disk->exists('avatars/unique.jpg'));
        $this->assertSame('HEAD', $mock->getLastRequest()->getMethod());
        $this->assertTrue($disk->delete('avatars/unique.jpg'));
        $this->assertSame('DELETE', $mock->getLastRequest()->getMethod());
    }
}
