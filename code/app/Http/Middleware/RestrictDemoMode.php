<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Services\Core\DemoService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class RestrictDemoMode
{
    protected $demoService;

    public function __construct(DemoService $demoService)
    {
        $this->demoService = $demoService;
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            if (config('demo.enabled')) {

                $methodConfig = config('demo.method_usage.' . strtoupper($request->method()), []);
                $method = strtoupper($request->method());
                $methodConfig = config('demo.method_usage.' . $method);
                $currentRoute = $request->route()->getName();
                
                if ($methodConfig !== null && !Arr::get($methodConfig, "enabled") && !in_array($currentRoute, Arr::get($methodConfig,'whitelisted_routes', []), true)) {
                    $message = Arr::get($methodConfig, "message", $this->demoService->getGlobalMessage());
                    $content = Arr::set(
                            array: $content,
                            key: 'message',
                            value: $message);
                    if ($request->expectsJson()) return new JsonResponse($content);
                    return redirect()->back()->with("error", $message);
                }

                $feature = $this->demoService->getFeatureForRoute($request);

                if(!$feature) return $next($request);
                if($this->demoService->isFeatureEnabled($feature)) return $next($request);
                
                if($this->demoService->isRestrictedRoute($request, $feature)) {
                    
                    $response = $request->expectsJson() 
                                ? new JsonResponse([], 200) 
                                : redirect()->back();
                    return $this->demoService->appendGlobalMessage($response, $request);
                }

                $restrictedKeys = $this->demoService->getRestrictedKeys($feature);
                
                if (empty($restrictedKeys)) {
                    return $this->demoService->appendGlobalMessage($feature, $request);
                }

                $originalData = $request->all();
                $hasRestrictedKeys = $this->demoService->hasRestrictedKeys($originalData, $restrictedKeys);

                $filteredData = $this->demoService->filterRestrictedKeys($originalData, $restrictedKeys);

                $filteredInput = array_filter($filteredData, function($value) {
                    return !($value instanceof \Illuminate\Http\UploadedFile);
                });

                $filteredFiles = array_filter($filteredData, function($value) {
                    return $value instanceof \Illuminate\Http\UploadedFile;
                });

                $request->replace($filteredInput);
                $request->files->replace($filteredFiles);

                $reflection = new \ReflectionClass($request);
                if ($reflection->hasProperty('convertedFiles')) {
                    $convertedFilesProperty = $reflection->getProperty('convertedFiles');
                    $convertedFilesProperty->setAccessible(true);
                    $convertedFilesProperty->setValue($request, null);
                }

                $response = $next($request);

                if (($hasRestrictedKeys) && $response->getStatusCode() === 200) {
                    return $this->demoService->appendGlobalMessage($response, $request);
                }

                return $response;
            }
        } catch (Exception $e) {
        }

        return $next($request);
    }
}