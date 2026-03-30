<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class UserDocumentationController extends Controller
{
    public function show(): View
    {
        $title = site_settings('user_docs_label') ?: translate('Documentation');
        return view('user.documentation', [
            'meta_data' => $this->metaData(['title' => $title]),
            'title'     => $title,
            'content'   => site_settings('user_docs_content') ?: '',
        ]);
    }

    public function n8nGuide(): View
    {
        return view('user.n8n_guide', [
            'meta_data' => $this->metaData(['title' => translate('n8n Integration Guide')]),
        ]);
    }
}
