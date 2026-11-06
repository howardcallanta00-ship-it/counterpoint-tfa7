<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Services\AvatarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class AccountsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_pages_and_navigation_work(): void
    {
        foreach (['/', '/about', '/customers', '/users', '/customers/create', '/users/create', '/up'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/')->assertSee(route('customers.index'))->assertSee(route('users.index'))->assertSee(route('about'));
        $customer = Customer::create($this->customer());
        $user = User::create($this->user());
        $this->get(route('customers.edit', $customer))->assertOk()->assertSee('Maria Santos');
        $this->get(route('users.edit', $user))->assertOk()->assertSee('maria.santos');
    }

    public function test_empty_states_are_actionable(): void
    {
        $this->get('/customers')->assertSee('No customers yet')->assertSee(route('customers.create'));
        $this->get('/users')->assertSee('No users yet')->assertSee(route('users.create'));
    }

    public function test_customer_create_edit_and_validated_data_only(): void
    {
        $this->post('/customers', $this->customer() + ['id' => 900])->assertRedirect('/customers')->assertSessionHas('success', 'Customer created.');
        $customer = Customer::firstOrFail();
        $this->assertNotSame(900, $customer->id);
        $this->put(route('customers.update', $customer), $this->customer(['full_name' => 'Maria Reyes', 'phone' => '']))->assertRedirect('/customers')->assertSessionHas('success', 'Customer updated.');
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'full_name' => 'Maria Reyes', 'phone' => null]);
    }

    public function test_customer_validation(): void
    {
        $this->post('/customers', [])->assertSessionHasErrors(['full_name', 'email']);
        $this->post('/customers', $this->customer(['email' => 'not-email']))->assertSessionHasErrors('email');
        $this->post('/customers', $this->customer(['full_name' => str_repeat('x', 101), 'phone' => str_repeat('0', 21)]))->assertSessionHasErrors(['full_name', 'phone']);
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_user_create_edit_and_unique_username(): void
    {
        $this->post('/users', $this->user() + ['password' => 'ignored'])->assertRedirect('/users')->assertSessionHas('success', 'User created.');
        $user = User::firstOrFail();
        $this->post('/users', $this->user())->assertSessionHasErrors('username');
        $this->put(route('users.update', $user), $this->user(['full_name' => 'Maria Reyes']))->assertRedirect('/users')->assertSessionHas('success', 'User updated.');
        User::create($this->user(['username' => 'another']));
        $this->put(route('users.update', $user), $this->user(['username' => 'another']))->assertSessionHasErrors('username');
        $this->assertSame('maria.santos', $user->fresh()->username);
    }

    public function test_user_required_and_length_validation(): void
    {
        $this->post('/users', [])->assertSessionHasErrors(['username', 'full_name']);
        $this->post('/users', $this->user(['username' => str_repeat('x', 51), 'full_name' => str_repeat('x', 101)]))->assertSessionHasErrors(['username', 'full_name']);
    }

    public function test_errors_and_old_input_on_edit(): void
    {
        $customer = Customer::create($this->customer());
        $url = route('customers.edit', $customer);
        $this->from($url)->put(route('customers.update', $customer), $this->customer(['full_name' => 'Keep this name', 'email' => 'broken']))->assertRedirect($url)->assertSessionHasInput('full_name', 'Keep this name')->assertSessionHasErrors('email');
        $this->get($url)->assertSee('value="Keep this name"', false)->assertSee('value="broken"', false)->assertSee('id="email-error"', false)->assertSee('aria-invalid="true"', false)->assertSee('data-error-summary', false);
        $user = User::create($this->user());
        $edit = route('users.edit', $user);
        $this->from($edit)->put(route('users.update', $user), $this->user(['username' => '', 'full_name' => 'Keep user name']))->assertSessionHasErrors('username');
        $this->get($edit)->assertSee('value="Keep user name"', false)->assertSee('id="username-error"', false);
    }

    public function test_missing_pages_and_records_are_404(): void
    {
        foreach (['/customers/999/edit', '/users/999/edit', '/missing'] as $url) {
            $this->get($url)->assertNotFound()->assertSee('Back to overview');
        }
        $this->put('/customers/999', $this->customer())->assertNotFound();
        $this->put('/users/999', $this->user())->assertNotFound();
    }

    public function test_jpeg_png_processing_storage_and_display(): void
    {
        foreach (['jpg', 'png'] as $extension) {
            $this->post('/users', $this->user(['username' => 'maria.'.$extension, 'avatar' => UploadedFile::fake()->image('portrait.'.$extension, 600, 400)]))->assertSessionHasNoErrors()->assertRedirect('/users');
            $user = User::where('username', 'maria.'.$extension)->firstOrFail();
            $this->assertMatchesRegularExpression('~^avatars/[a-f0-9-]+\.jpg$~', $user->avatar);
            Storage::disk('public')->assertExists($user->avatar);
            $size = getimagesizefromstring(Storage::disk('public')->get($user->avatar));
            $this->assertSame([256, 256], [$size[0], $size[1]]);
            $this->assertSame('image/jpeg', $size['mime']);
            $this->get('/users')->assertSee(Storage::disk('public')->url($user->avatar))->assertSee('Profile photo of Maria Santos');
        }
    }

    public function test_crop_uses_image_center(): void
    {
        $image = imagecreatetruecolor(600, 256);
        imagefilledrectangle($image, 0, 0, 599, 255, imagecolorallocate($image, 255, 0, 0));
        imagefilledrectangle($image, 172, 0, 427, 255, imagecolorallocate($image, 0, 255, 0));
        ob_start();
        imagepng($image);
        $bytes = ob_get_clean();
        imagedestroy($image);
        $path = app(AvatarService::class)->store(UploadedFile::fake()->createWithContent('center.png', $bytes));
        $result = imagecreatefromstring(Storage::disk('public')->get($path));
        $pixel = imagecolorsforindex($result, imagecolorat($result, 128, 128));
        $this->assertGreaterThan(240, $pixel['green']);
        $this->assertLessThan(15, $pixel['red']);
        imagedestroy($result);
    }

    public function test_exif_orientation_is_corrected_before_crop(): void
    {
        $image = imagecreatetruecolor(600, 256);
        imagefilledrectangle($image, 0, 0, 299, 255, imagecolorallocate($image, 255, 0, 0));
        imagefilledrectangle($image, 300, 0, 599, 255, imagecolorallocate($image, 0, 255, 0));
        ob_start();
        imagejpeg($image, null, 100);
        $jpeg = ob_get_clean();
        imagedestroy($image);
        // Minimal EXIF tag: orientation 6 means rotate 90 degrees clockwise.
        $exif = "Exif\0\0II".pack('vV', 42, 8).pack('v', 1).pack('vvVvvV', 0x0112, 3, 1, 6, 0, 0);
        $jpeg = substr($jpeg, 0, 2)."\xff\xe1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
        $path = app(AvatarService::class)->store(UploadedFile::fake()->createWithContent('rotated.jpg', $jpeg));
        $result = imagecreatefromstring(Storage::disk('public')->get($path));
        $top = imagecolorsforindex($result, imagecolorat($result, 128, 30));
        $bottom = imagecolorsforindex($result, imagecolorat($result, 128, 220));
        $this->assertGreaterThan(240, $top['red']);
        $this->assertGreaterThan(240, $bottom['green']);
        imagedestroy($result);
    }

    public function test_invalid_oversized_and_spoofed_uploads(): void
    {
        foreach ([UploadedFile::fake()->create('file.pdf', 10, 'application/pdf'), UploadedFile::fake()->image('animated.gif'), UploadedFile::fake()->image('large.jpg')->size(2049), UploadedFile::fake()->createWithContent('pretend.jpg', '%PDF-1.4 Not an image'), UploadedFile::fake()->image('huge.png', 6001, 1)] as $file) {
            $this->post('/users', $this->user(['avatar' => $file]))->assertSessionHasErrors('avatar');
        }
        $this->assertDatabaseCount('users', 0);
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    public function test_no_upload_preserves_existing_photo(): void
    {
        Storage::disk('public')->put('avatars/old.jpg', 'original');
        $user = User::create($this->user(['avatar' => 'avatars/old.jpg']));
        $this->put(route('users.update', $user), $this->user(['full_name' => 'Updated', 'avatar' => null]))->assertSessionHasNoErrors();
        $this->assertSame('avatars/old.jpg', $user->fresh()->avatar);
        Storage::disk('public')->assertExists('avatars/old.jpg');
    }

    public function test_replacement_removes_old_file_after_save(): void
    {
        Storage::disk('public')->put('avatars/old.jpg', 'original');
        $user = User::create($this->user(['avatar' => 'avatars/old.jpg']));
        User::updating(function () {
            Storage::disk('public')->assertExists('avatars/old.jpg');
        });
        try {
            $this->put(route('users.update', $user), $this->user(['avatar' => UploadedFile::fake()->image('new.jpg')]))->assertSessionHasNoErrors();
        } finally {
            User::flushEventListeners();
        }
        Storage::disk('public')->assertMissing('avatars/old.jpg');
        Storage::disk('public')->assertExists($user->fresh()->avatar);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_database_failure_keeps_old_record_and_cleans_new_file(): void
    {
        Storage::disk('public')->put('avatars/old.jpg', 'original');
        $user = User::create($this->user(['avatar' => 'avatars/old.jpg']));
        User::updating(function () {
            throw new RuntimeException('Forced database failure');
        });
        try {
            $this->put(route('users.update', $user), $this->user(['full_name' => 'Should roll back', 'avatar' => UploadedFile::fake()->image('new.jpg')]))->assertSessionHasErrors('avatar');
        } finally {
            User::flushEventListeners();
        }
        $this->assertSame('avatars/old.jpg', $user->fresh()->avatar);
        $this->assertSame('Maria Santos', $user->fresh()->full_name);
        $this->assertSame(['avatars/old.jpg'], Storage::disk('public')->allFiles());
    }

    public function test_failed_create_rolls_back_row_and_cleans_file(): void
    {
        User::created(function () {
            throw new RuntimeException('Forced create failure');
        });
        try {
            $this->post('/users', $this->user(['avatar' => UploadedFile::fake()->image('new.jpg')]))->assertSessionHasErrors('avatar');
        } finally {
            User::flushEventListeners();
        }
        $this->assertDatabaseCount('users', 0);
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    public function test_processor_failure_is_field_specific_and_preserves_photo(): void
    {
        Storage::disk('public')->put('avatars/old.jpg', 'original');
        $user = User::create($this->user(['avatar' => 'avatars/old.jpg']));
        $service = \Mockery::mock(AvatarService::class)->makePartial();
        $service->shouldReceive('store')->once()->andThrow(new RuntimeException('Forced processor failure'));
        $this->app->instance(AvatarService::class, $service);
        $this->put(route('users.update', $user), $this->user(['avatar' => UploadedFile::fake()->image('new.jpg')]))->assertSessionHasErrors('avatar');
        $this->assertSame('avatars/old.jpg', $user->fresh()->avatar);
        $this->assertSame(['avatars/old.jpg'], Storage::disk('public')->allFiles());
    }

    public function test_partial_storage_failure_cleans_orphan(): void
    {
        $disk = Storage::disk('public');
        $disk->put('avatars/old.jpg', 'original');
        $user = User::create($this->user(['avatar' => 'avatars/old.jpg']));
        $failing = \Mockery::mock($disk)->makePartial();
        $failing->shouldReceive('put')->once()->andReturnUsing(function ($path, $bytes) use ($disk) {
            $disk->put($path, $bytes);
            throw new RuntimeException('Forced partial write failure');
        });
        Storage::shouldReceive('disk')->with('public')->andReturn($failing);
        $this->put(route('users.update', $user), $this->user(['avatar' => UploadedFile::fake()->image('new.jpg')]))->assertSessionHasErrors('avatar');
        $this->assertSame('avatars/old.jpg', $user->fresh()->avatar);
        $this->assertSame(['avatars/old.jpg'], $disk->allFiles());
    }

    public function test_missing_and_null_avatar_placeholder(): void
    {
        User::create($this->user());
        User::create($this->user(['username' => 'missing', 'avatar' => 'avatars/missing.jpg']));
        $response = $this->get('/users')->assertOk();
        $this->assertSame(2, substr_count($response->getContent(), 'src="'.asset('images/avatar-placeholder.svg').'"'));
        $response->assertDontSee('/storage/avatars/missing.jpg');
    }

    public function test_seeding_twice_preserves_edits_and_has_no_duplicates(): void
    {
        $this->seed();
        $this->assertDatabaseCount('customers', 5);
        $this->assertDatabaseCount('users', 5);
        Customer::first()->update(['full_name' => 'Edited customer']);
        User::first()->update(['full_name' => 'Edited user']);
        $this->seed();
        $this->assertDatabaseCount('customers', 5);
        $this->assertDatabaseCount('users', 5);
        $this->assertDatabaseHas('customers', ['full_name' => 'Edited customer']);
        $this->assertDatabaseHas('users', ['full_name' => 'Edited user']);
    }

    public function test_exact_schema_and_avatar_rollback(): void
    {
        $this->assertEqualsCanonicalizing(['id', 'username', 'full_name', 'created_at', 'avatar'], Schema::getColumnListing('users'));
        $this->assertEqualsCanonicalizing(['id', 'full_name', 'email', 'phone', 'created_at'], Schema::getColumnListing('customers'));
        $migration = require database_path('migrations/2026_11_06_000002_add_avatar_to_users.php');
        $migration->down();
        $this->assertFalse(Schema::hasColumn('users', 'avatar'));
        $migration->up();
        $this->assertTrue(Schema::hasColumn('users', 'avatar'));
    }

    public function test_output_is_escaped(): void
    {
        Customer::create($this->customer(['full_name' => '<script>alert(1)</script>']));
        $this->get('/customers')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    private function customer(array $overrides = []): array
    {
        return array_replace(['full_name' => 'Maria Santos', 'email' => 'maria@example.test', 'phone' => 'DEMO-000-0001'], $overrides);
    }

    private function user(array $overrides = []): array
    {
        return array_replace(['full_name' => 'Maria Santos', 'username' => 'maria.santos'], $overrides);
    }
}
