<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThemeService;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class ThemeController extends Controller
{
    protected $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    /**
     * Display theme management page
     *
     * @return View
     */
    public function index(): View
    {
        $themes = $this->themeService->getAllThemes();
        $activeTheme = $this->themeService->getActiveTheme();

        return view('admin.theme.index', [
            'title' => translate('Theme Management'),
            'themes' => $themes,
            'activeTheme' => $activeTheme,
        ]);
    }

    /**
     * Activate a theme
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function activate(Request $request): JsonResponse
    {
        $request->validate([
            'theme_id' => 'required|integer|exists:themes,id',
        ]);

        $result = $this->themeService->activateTheme($request->theme_id);

        return response()->json($result);
    }

    /**
     * Install a new theme from ZIP
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function install(Request $request): RedirectResponse
    {
        $request->validate([
            'theme_file' => 'required|file|mimes:zip|max:51200', // 50MB max
        ]);

        $result = $this->themeService->installTheme($request->file('theme_file'));

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Delete a theme
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'theme_id' => 'required|integer|exists:themes,id',
        ]);

        $result = $this->themeService->deleteTheme($request->theme_id);

        return response()->json($result);
    }

    /**
     * Update theme configuration
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function updateConfig(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'config' => 'required|array',
        ]);

        $result = $this->themeService->updateThemeConfig($id, $request->config);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Show theme configuration page
     *
     * @param int $id
     * @return View
     */
    public function configure(int $id): View
    {
        $theme = Theme::findOrFail($id);

        return view('admin.theme.configure', [
            'title' => translate('Configure Theme'),
            'theme' => $theme,
        ]);
    }
}
