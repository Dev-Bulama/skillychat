<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AiModuleType;
use App\Enums\FileKey;
use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContentRequest;
use App\Http\Requests\ImageContentRequest;
use App\Http\Requests\UploadRequest;
use App\Http\Services\ContentService;
use App\Models\Admin\Category;
use App\Models\AiTemplate;
use App\Models\Content;
use Illuminate\Http\Request;
use App\Traits\ModelAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
class CannedImageContentController extends Controller
{
    use ModelAction;
    public $contentService;

    /**
     *
     * @return void
     */
    public function __construct()
    {

        //check permissions middleware
        $this->middleware(['permissions:view_content'])->only(['list']);
        $this->middleware(['permissions:create_content'])->only(['store', 'create']);
        $this->middleware(['permissions:update_content'])->only(['updateStatus', 'update', 'edit', 'bulk']);
        $this->middleware(['permissions:delete_content'])->only(['destroy', 'bulk']);

        $this->contentService = new ContentService();

    }


    /**
     * Content list
     *
     * @return View
     */
    public function list(): View
    {

        return view('admin.image_content.list', [

            'breadcrumbs' => ['Home' => 'admin.home', 'Ai Image Contents' => null],
            'title' => 'Manage Predefined Image Content',
            'contents' => Content::with('file')
                ->whereHas('file')
                ->whereNull('user_id')
                ->search(['name'])
                ->image()
                ->latest()
                ->paginate(8)
                ->appends(request()->all()),

            'categories' => Category::template()
                ->doesntHave('parent')
                ->get(),

            'templates' => AiTemplate::active()->get(),


        ]);
    }

    public function gallery(): View
    {

        return view('admin.image_content.gallery', [

            'breadcrumbs' => ['Home' => 'admin.home', 'Image Gallery' => null],
            'title' => 'Manage Predefined Image Content',
            'contents' => Content::with('file')
                ->whereHas('file')
                ->whereNull('user_id')
                ->search(['name'])
                ->latest()
                ->paginate(paginateNumber())
                ->appends(request()->all()),

            'categories' => Category::template()
                ->doesntHave('parent')
                ->image()
                ->get(),

            'templates' => AiTemplate::active()->get(),


        ]);
    }




    /**
     * store a  new content
     *
     * @param ContentRequest $request
     * @return RedirectResponse | string
     */
    public function store(ImageContentRequest $request): RedirectResponse|string
    {
        $response = $this->contentService->imageSave($request);

        if ($request->ajax()) {
            return json_encode([
                "message" => translate('Content created successfully'),
                "status" => true,
            ]);
        }
        return back()->with($response);
    }




    /**
     * Update a specific Article
     *
     * @param ContentRequest $request
     * @return RedirectResponse
     */
    public function update(ContentRequest $request): RedirectResponse
    {

        $content = Content::whereNull('user_id')
            ->where("id", $request->input('id'))->firstOrfail();

        return back()->with($this->contentService->update($request, $content));
    }

    /**
     * Update a specific Article status
     *
     * @param Request $request
     * @return string
     */
    public function updateStatus(Request $request): string
    {

        $request->validate([
            'id' => 'required|exists:contents,uid',
            'status' => ['required', Rule::in(StatusEnum::toArray())],
            'column' => ['required', Rule::in(['status'])],
        ]);

        return $this->changeStatus($request->except("_token"), [
            "model" => new Content(),
        ]);
    }


    public function destroy(string|int $id): RedirectResponse
    {

        $content = Content::whereNull('user_id')->where('id', $id)->firstOrfail();
        $content->delete();
        return back()->with(response_status('Item deleted succesfully'));
    }


    /**
     * Bulk action
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function bulk(Request $request): RedirectResponse
    {

        try {
            $response = $this->bulkAction($request, [
                "model" => new Content(),
            ]);

        } catch (\Exception $exception) {
            $response = \response_status($exception->getMessage(), 'error');
        }
        return back()->with($response);
    }

    public function upload(UploadRequest $request)
    {
        $images = $request->input('images');

        try {

            foreach ($images as $index => $file) {

                $fileName = $file->getClientOriginalName();


                $content = new Content();
                $content->name = $fileName;
                $content->slug = make_slug($fileName);
                $content->type      =  AiModuleType::IMAGE->value;
                $content->save();



                $this->saveFile(
                    $content,
                    $this->storeFile(
                        file: $file,
                        location: config("settings")['file_path']['content']['path'],
                    )
                    ,
                    FileKey::CONTENT_FILE->value
                );

            }
        } catch (\Throwable $th) {

            $response =  response_status('Content uploaded failed' , 'error');
        }

        $response=  response_status('Content uploaded successfully');

        return back()->with($response);

    }
}
