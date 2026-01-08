<?php

namespace App\Http\Controllers\User;

use App\Enums\AiModuleType;
use App\Enums\FileKey;
use App\Enums\PlanDuration;
use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContentRequest;
use App\Http\Requests\ImageContentRequest;
use App\Http\Requests\UploadRequest;
use App\Http\Services\AiService;
use App\Http\Services\ContentService;
use App\Models\Admin\Category;
use App\Models\AiTemplate;
use App\Models\Content;
use App\Traits\ModelAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AiImageController extends Controller
{
    use ModelAction ;
    protected  $user,$contentService , $templates ,$aiService ,$remainingToken ;

    public function __construct(){

        $this->contentService  = new ContentService();
        $this->aiService       =  new AiService();
        $this->middleware(function ($request, $next) {

            $this->user             = auth_user('web');
            $subscription           = $this->user->runningSubscription;
            $templateAccess         = $subscription ? (array)subscription_value($subscription,"image_template_access",true) :[];
            $this->templates        = AiTemplate::whereIn('id',$templateAccess)->get();
            $this->remainingToken   = $subscription ? $subscription->remaining_word_balance : 0;

            return $next($request);
        });
    }

    /**
     * Content list
     *
     * @return View
     */
    public function list() :View{


        $accessCategories = (array)@$this->templates->pluck('category_id')->unique()->toArray();

        return view('user.image_content.list',[

            'meta_data'    => $this->metaData(['title'=> translate("Gallery Images")]),

            'contents'     => Content::where('user_id',$this->user->id)
                                        ->search(['name'])
                                        ->image()
                                        ->latest()
                                        ->paginate(8)
                                        ->appends(request()->all()),

            'categories'  => Category::template()
                                        ->doesntHave('parent')
                                        ->image()
                                        ->whereIn('id',$accessCategories)
                                        ->get(),

            'templates'  =>     $this->templates

        ]);
    }

    public function gallery() :View{



        return view('user.image_content.gallery',[

            'meta_data'    => $this->metaData(['title'=> translate("Image Gallery")]),

            'contents'     => Content::where('user_id',$this->user->id)
                                        ->search(['name'])
                                        ->latest()
                                        ->paginate(paginateNumber())
                                        ->appends(request()->all()),


        ]);
    }


    /**
     * Update a specific Article
     *
     * @param ContentRequest $request
     * @return RedirectResponse
     */
    public function store(ImageContentRequest $request) :RedirectResponse {


        $imageUrls = $request->input('image_urls');
        $baseName = $request->input('name');

        if($imageUrls){

            if (empty($imageUrls) || !is_array($imageUrls)) {
                return back()->with(response_status('Could not save images , invalid url'));

            }

            try {

                foreach($imageUrls as $index=>$url){

                    $fileContent = @file_get_contents($url);

                    if ($fileContent === false) {
                        return back()->with(response_status(translate("Failed to download image: ") . $url));
                    }

                    $fileName =  uniqid() . '.jpg';

                    $content            =  new Content();
                    $content->name      =  $fileName;
                    $content->slug      =  make_slug($fileName);
                    $content->user_id   =  $this->user->id;
                    $content->type      =  AiModuleType::IMAGE->value;

                    $content->save();


                    $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.jpg';
                    file_put_contents($tempPath, $fileContent);

                    $uploadedFile = new \Illuminate\Http\UploadedFile(
                        $tempPath,
                        basename($tempPath),
                        'image/jpeg',
                        null,
                        true
                    );


                    $this->saveFile($content ,$this->storeFile(
                        file        : $uploadedFile,
                        location    : config('settings')['file_path']['content']['path'],
                     )
                     , FileKey::CONTENT_FILE->value);



                    @unlink($tempPath);

                }
            } catch (\Throwable $th) {

            }
        }

        return  back()->with(response_status('Content created successfully'));
    }


    public function upload(UploadRequest $request)
    {
        $images = $request->input('images');


        try {

            foreach ($images as $index => $file) {

                $fileName = $file->getClientOriginalName();


                $content = new Content();

                $content->name      = $fileName;
                $content->slug      = make_slug($fileName);
                $content->user_id   =  $this->user->id;
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






    /**
     * Update a specific Article
     *
     * @param ContentRequest $request
     * @return RedirectResponse
     */
    public function update(ContentRequest $request) :RedirectResponse {

        $content = Content::where('user_id',$this->user->id)
                       ->where("id",$request->input('id'))->firstOrfail();

        return  back()->with($this->contentService->update($request , $content));
    }



    /**
     * Update a specific Article status
     *
     * @param Request $request
     * @return string
     */
    public function updateStatus(Request $request) :string{

        $request->validate([
            'id'      => 'required|exists:contents,uid',
            'status'  => ['required',Rule::in(StatusEnum::toArray())],
            'column'  => ['required',Rule::in(['status'])],
        ]);

        return $this->changeStatus($request->except("_token"),[
            "model"      => new Content(),
            "user_id"    => $this->user->id,
        ]);
    }


    public function destroy(string | int $id) :RedirectResponse{

        $content  = Content::where('user_id',$this->user->id)->where('id',$id)->firstOrfail();
        $content->delete();
        return  back()->with(response_status('Item deleted succesfully'));
    }


    public function generateImage(Request $request): string
    {
        try {
            $templateRules   =  $this->aiService->setImageRules($request);
            $request->validate(Arr::get($templateRules, 'rules', []),Arr::get($templateRules, 'messages', []));

            $response ['status']  =  false;
            $response ['message'] =  translate("Insufficient Image tokens to utilize the template. Please acquire additional tokens for access");

            if($request->input('custom_prompt') == StatusEnum::false->status()){
                $template        = Arr::get($templateRules,'template');
                $accessTemplates = $this->templates ? @$this->templates->pluck('id')->toArray() :[];
                if(!in_array(@$template->id, $accessTemplates)) {
                    return json_encode([
                        "status"       => false,
                        "message"      => translate("AI template access unavailable. Ensure an active subscription for utilization. Thank you for your understanding"),
                    ]);
                }
            }

            if($this->remainingToken == PlanDuration::UNLIMITED->value || $this->remainingToken > (int) $request->input('max_result') ){
                $request->validate(Arr::get($templateRules, 'rules', []));
                $response =  $request->input('custom_prompt') == StatusEnum::false->status()
                                        ? $this->aiService->generateImageContent($request,$templateRules['template'])
                                        : $this->aiService->generateCustomPromptImageContent($request) ;
            }



            return json_encode($response);

        } catch (\Exception $ex) {
            return json_encode([
                "status"       => false,
                "message"      => $ex->getMessage()
            ]);
        }
    }
}
