<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureFlagController extends Controller
{
    public function index(): View
    {
        $flags = FeatureFlag::orderBy('label')->get();

        return view('admin.feature_flags.index', [
            'title' => translate('Feature Flags'),
            'flags' => $flags,
        ]);
    }

    public function toggle(Request $request): RedirectResponse
    {
        $request->validate(['id' => 'required|integer|exists:feature_flags,id']);

        $flag = FeatureFlag::findOrFail($request->id);
        $flag->toggle();

        $state = $flag->is_enabled ? 'enabled' : 'disabled';

        return back()->with(response_status("Feature '{$flag->label}' has been {$state}."));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $flag = FeatureFlag::findOrFail($id);

        $flag->setEnabled((bool) $request->boolean('is_enabled'));

        return back()->with(response_status('Feature flag updated successfully.'));
    }
}
