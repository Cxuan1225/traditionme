<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Database\Seeders\SecurityRbacSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

test('admin with products.view permission can access product management screen', function (): void {
    $this->seed(SecurityRbacSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get(route('products.index'));

    $response->assertOk();
});

test('non privileged user cannot access product management screen', function (): void {
    $this->seed(SecurityRbacSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this->actingAs($user)->get(route('products.index'));

    $response->assertForbidden();
});

test('admin can create update and delete product', function (): void {
    $this->seed(SecurityRbacSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'web'));

    $createResponse = $this->actingAs($admin)->post(route('products.store'), [
        'name' => 'Songket Classic',
        'slug' => 'songket-classic',
        'category' => 'Women',
        'price_in_sen' => 19900,
        'original_price_in_sen' => 24900,
        'badge' => 'New',
        'gradient' => 'from-rose-100 via-orange-50 to-amber-100',
        'is_active' => true,
    ]);

    $createResponse->assertCreated();
    $this->assertDatabaseHas('products', ['slug' => 'songket-classic']);

    $product = Product::query()->where('slug', 'songket-classic')->firstOrFail();

    $updateResponse = $this->actingAs($admin)->put(route('products.update', $product), [
        'name' => 'Songket Classic Updated',
        'slug' => 'songket-classic-updated',
        'category' => 'Women',
        'price_in_sen' => 20900,
        'original_price_in_sen' => 25900,
        'badge' => 'Best Seller',
        'gradient' => 'from-red-100 via-orange-50 to-yellow-100',
        'is_active' => true,
    ]);

    $updateResponse->assertOk();
    $this->assertDatabaseHas('products', ['slug' => 'songket-classic-updated']);

    $deleteResponse = $this->actingAs($admin)->delete(route('products.destroy', $product->fresh()));

    $deleteResponse->assertOk();
    $this->assertDatabaseMissing('products', ['slug' => 'songket-classic-updated']);
});

test('admin can create product with uploaded image', function (): void {
    Storage::fake('public');
    $this->seed(SecurityRbacSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'web'));

    $createResponse = $this->actingAs($admin)->post(route('products.store'), [
        'name' => 'Batik Deluxe',
        'slug' => 'batik-deluxe',
        'category' => 'Women',
        'price_in_sen' => 25900,
        'original_price_in_sen' => 29900,
        'badge' => 'Fresh',
        'gradient' => 'from-emerald-100 via-teal-50 to-cyan-100',
        'image' => UploadedFile::fake()->image('batik-deluxe.jpg'),
        'is_active' => true,
    ]);

    $createResponse->assertCreated();

    $product = Product::query()->where('slug', 'batik-deluxe')->firstOrFail();

    expect($product->image_url)->toStartWith('/storage/products/');
    Storage::disk('public')->assertExists(str_replace('/storage/', '', (string) $product->image_url));
});

test('admin replacing an uploaded image removes the old managed file', function (): void {
    Storage::fake('public');
    $this->seed(SecurityRbacSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'web'));

    $createResponse = $this->actingAs($admin)->post(route('products.store'), [
        'name' => 'Tenun Luxe',
        'slug' => 'tenun-luxe',
        'category' => 'Women',
        'price_in_sen' => 31900,
        'original_price_in_sen' => 35900,
        'badge' => 'Editor Pick',
        'gradient' => 'from-fuchsia-100 via-rose-50 to-orange-100',
        'image' => UploadedFile::fake()->image('tenun-luxe-old.jpg'),
        'is_active' => true,
    ]);

    $createResponse->assertCreated();

    $product = Product::query()->where('slug', 'tenun-luxe')->firstOrFail();
    $oldImagePath = str_replace('/storage/', '', (string) $product->image_url);

    Storage::disk('public')->assertExists($oldImagePath);

    $updateResponse = $this->actingAs($admin)->post(route('products.update', $product), [
        '_method' => 'PUT',
        'name' => 'Tenun Luxe',
        'slug' => 'tenun-luxe',
        'category' => 'Women',
        'price_in_sen' => 32900,
        'original_price_in_sen' => 36900,
        'badge' => 'Editor Pick',
        'gradient' => 'from-fuchsia-100 via-rose-50 to-orange-100',
        'image' => UploadedFile::fake()->image('tenun-luxe-new.jpg'),
        'is_active' => true,
    ]);

    $updateResponse->assertOk();

    $product->refresh();
    $newImagePath = str_replace('/storage/', '', (string) $product->image_url);

    expect($product->image_url)->toStartWith('/storage/products/');
    expect($newImagePath)->not->toBe($oldImagePath);
    Storage::disk('public')->assertMissing($oldImagePath);
    Storage::disk('public')->assertExists($newImagePath);
});
