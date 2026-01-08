<?php
namespace App\Http\Services;

use App\Enums\AiModuleType;
use App\Enums\FileKey;
use App\Enums\StatusEnum;
use App\Models\Content;
use App\Traits\ModelAction;
use Illuminate\Http\Request;

class ContentService
{

    use ModelAction;


    /**
     * store a content
     *
     * @param Request $request
     * @return array
     */
    public function save(Request $request): array
    {

        $content = new Content();
        $content->name = $request->input('name');
        $content->content = $request->input('content');
        $content->type = AiModuleType::TEXT->value;

        $content->save();

        return response_status('Content created successfully');

    }

    public function imageSave(Request $request): array
    {

        $imageUrls = $request->input('image_urls');


        if ($imageUrls) {

            if (empty($imageUrls) || !is_array($imageUrls)) {
                return response_status('Could not save images , invalid url');

            }

            try {

                foreach ($imageUrls as $index => $url) {


                    $fileContent = @file_get_contents($url);

                    if ($fileContent === false) {
                        return response_status(translate("Failed to download image: ") . $url);
                    }

                    $fileName = uniqid() . '.jpg';

                    $content = new Content();
                    $content->name = $fileName;
                    $content->slug = make_slug($fileName);
                    $content->type = AiModuleType::IMAGE->value;

                    $content->save();


                    $tempPath = sys_get_temp_dir() . '/' . $fileName;
                    file_put_contents($tempPath, $fileContent);

                    $uploadedFile = new \Illuminate\Http\UploadedFile(
                        $tempPath,
                        basename($tempPath),
                        'image/jpeg',
                        null,
                        true
                    );


                    $this->saveFile(
                        $content,
                        $this->storeFile(
                            file: $uploadedFile,
                            location: config('settings')['file_path']['content']['path'],
                        )
                        ,
                        FileKey::CONTENT_FILE->value
                    );

                    @unlink($tempPath);

                }
            } catch (\Throwable $th) {


            }
        }

        return response_status('Content created successfully');


    }


    public function videoSave(Request $request)
    {



        $videoUrls = $request->input('video_urls');

        try {
            foreach ($videoUrls as $url) {

                $client = new \GuzzleHttp\Client();
                $response = $client->get($url, ['stream' => true]);


                $contentType = $response->getHeaderLine('Content-Type');
                if (!str_contains($contentType, 'video/')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Invalid video file: $url",
                    ], 400);
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


                $filePath = $this->storeFile(
                    file: $uploadedFile,
                    location: config('settings')['file_path']['content']['path'],
                );


                $content = new Content();
                $content->name = $fileName;
                $content->slug = make_slug($fileName);
                $content->type = AiModuleType::VIDEO->value;
                $content->save();

                // Associate file with content
                $this->saveFile($content, $filePath, FileKey::CONTENT_FILE->value);

                // Clean up temporary file
                unlink($tempPath);
            }



        } catch (\Exception $e) {


        }

        return response_status('Content created successfully');

    }



    /**
     * Update a content
     *
     * @param Request $request
     * @param Content $content
     * @return void
     */
    public function update(Request $request, Content $content): array
    {

        $content->name = $request->input('name');
        $content->content = $request->input('content');
        $content->save();
        return response_status('Content updated successfully');
    }


}
