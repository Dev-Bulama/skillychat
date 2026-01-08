<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\VideoContentRequest;
use Illuminate\Http\Request;
use App\Enums\AiModuleType;
use App\Enums\FileKey;
use App\Enums\PlanDuration;
use App\Enums\StatusEnum;
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
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AiVideoController extends Controller
{
    use ModelAction ;
    protected  $user,$contentService , $templates ,$aiService ,$remainingToken ;

    public function __construct(){

        $this->contentService  = new ContentService();
        $this->aiService       =  new AiService();
        $this->middleware(function ($request, $next) {

            $this->user             = auth_user('web');
            $subscription           = $this->user->runningSubscription;
            $templateAccess         = $subscription ? (array)subscription_value($subscription,"video_template_access",true) :[];
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

        return view('user.video_content.list',[

            'meta_data'    => $this->metaData(['title'=> translate("Gallery Images")]),

            'contents'     => Content::where('user_id',$this->user->id)
                                        ->search(['name'])
                                        ->latest()
                                        ->video()
                                        ->paginate(8)
                                        ->appends(request()->all()),

            'categories'  => Category::template()
                                        ->doesntHave('parent')
                                        ->whereIn('id',$accessCategories)
                                        ->video()
                                        ->get(),

            'templates'  =>     $this->templates

        ]);
    }

    public function gallery() :View{



        return view('user.video_content.gallery',[

            'meta_data'    => $this->metaData(['title'=> translate("Video Gallery")]),

            'contents'     => Content::where('user_id',$this->user->id)
                                        ->search(['name'])
                                        ->latest()
                                        ->video()
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
    public function store(VideoContentRequest $request) :RedirectResponse {


        $videoUrls = $request->input('video_urls');

        if($videoUrls){

            if (empty($videoUrls) || !is_array($videoUrls)) {
                return back()->with(response_status('Could not save video , invalid url'));

            }

            try {

                foreach($videoUrls as $index=>$url){

                    $client = new \GuzzleHttp\Client();
                    $response = $client->get($url, ['stream' => true]);

                    $contentType = $response->getHeaderLine('Content-Type');
                    if (!str_contains($contentType, 'video/')) {
                        return back()->with('error', "Invalid video file: $url");
                    }

                    $fileName = time() . '_' . uniqid() . '.mp4';
                    $tempPath = sys_get_temp_dir() . '/' . $fileName;

                    file_put_contents($tempPath, $response->getBody()->getContents());

                    $uploadedFile = new \Illuminate\Http\UploadedFile(
                        $tempPath,
                        $fileName,
                        'video/mp4',
                        null,
                        true
                    );

                    // Store file
                    $filePath = $this->storeFile(
                        file: $uploadedFile,
                        location: config('settings')['file_path']['content']['path'],
                    );

                    $content = new Content();
                    $content->name = $fileName;
                    $content->slug = make_slug($fileName);
                    $content->user_id = $this->user->id;
                    $content->type = AiModuleType::VIDEO->value;
                    $content->save();

                    $this->saveFile($content, $filePath, FileKey::CONTENT_FILE->value);

                    unlink($tempPath);

                }
            } catch (\Throwable $th) {

            }
        }

        return  back()->with(response_status('Content created successfully'));
    }


    public function upload(Request $request)
    {
        $videos = $request->input('videos');


        try {

            foreach ($videos as $index => $file) {


                $fileName = time() . '_' . uniqid() . '.mp4';


                $content            = new Content();
                $content->name      = $fileName;
                $content->slug      = make_slug($fileName);
                $content->user_id   =  $this->user->id;
                $content->type      =  AiModuleType::VIDEO->value;
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

            dd($th->getMessage());

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


    public function generateVideo(Request $request): string
    {
        try {
            $templateRules   =  $this->aiService->setVideoRules($request);
            $request->validate(Arr::get($templateRules, 'rules', []),Arr::get($templateRules, 'messages', []));
            $result = 1;

            $response ['status']  =  false;
            $response ['message'] =  translate("Insufficient Video tokens to utilize the template. Please acquire additional tokens for access");

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

            if($this->remainingToken == PlanDuration::UNLIMITED->value || $this->remainingToken > (int) $result ){
                $request->validate(Arr::get($templateRules, 'rules', []));
                $response =  $request->input('custom_prompt') == StatusEnum::false->status()
                                        ? $this->aiService->generateVideoContent($request,$templateRules['template'])
                                        : $this->aiService->generateCustomPromptVideoContent($request) ;
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
