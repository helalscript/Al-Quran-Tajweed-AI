<?php

namespace App\Http\Controllers\Web;

use App\Models\DynamicPage;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class PageController
{
    public function show(DynamicPage $dynamic_page): Response|HttpResponse
    {
        if ($dynamic_page->status !== 'active') {
            return response(status: 404);
        }

        return Inertia::render('page', [
            'page' => [
                'title' => $dynamic_page->page_title,
                'slug' => $dynamic_page->page_slug,
                'content' => $dynamic_page->page_content,
            ],
        ]);
    }
}

