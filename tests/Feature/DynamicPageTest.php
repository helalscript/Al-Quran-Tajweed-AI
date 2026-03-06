<?php

use App\Models\DynamicPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows admin to see dynamic pages index', function () {
    $this->actingAs(
        \App\Models\User::factory()->create(['role' => 'admin']),
    );

    DynamicPage::factory()->create([
        'page_title' => 'Privacy Policy',
        'page_slug' => 'privacy-policy',
        'status' => 'active',
    ]);

    $response = $this->get('/admin/pages');

    $response->assertSuccessful();
});

it('shows active page publicly by slug', function () {
    $page = DynamicPage::factory()->create([
        'page_title' => 'Terms & Conditions',
        'page_slug' => 'terms-and-conditions',
        'status' => 'active',
        'page_content' => '<p>Sample content</p>',
    ]);

    $response = $this->get("/page/{$page->page_slug}");

    $response->assertSuccessful();
    $response->assertSee('Sample content', escape: false);
});

