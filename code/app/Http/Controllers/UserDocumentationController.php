<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class UserDocumentationController extends Controller
{
    public function show(): View
    {
        return view('user.documentation', [
            'title'   => site_settings('user_docs_label') ?: translate('Documentation'),
            'content' => site_settings('user_docs_content') ?: '',
        ]);
    }

    public function n8nGuide(): View
    {
        return view('user.n8n_guide');
    }
}
