<?php
namespace App\Http\Services;

use App\Enums\StatusEnum;
use App\Models\Core\Language;
use App\Models\Core\Translation;
use App\Models\ModelTranslation;
use Illuminate\Support\Str;

class LanguageService
{



    public function store($request): array
    {

        $country = explode("//", $request->name);
        $code = $request->code ? $request->code : strtolower($country[1]);
        if (Language::where('code', $code)->exists()) {
            $response['status'] = "error";
            $response['message'] = translate('This Language Is Already Added !! Try Another');
        } else {

            $language = Language::create([
                'name' => $country[0],
                'code' => $code,
                'is_default' => (StatusEnum::false)->status(),
                'ltr' => $request->input('direction'),
                'display_name' => $request->input('display_name')
            ]);

            try {
                $translations = Translation::where('code', 'en')->get();
                $translationsToCreate = [];

                foreach ($translations as $k) {
                    $translationsToCreate[] = [
                        "uid" => Str::random(40),
                        'code' => $language->code,
                        'key' => $k->key,
                        'value' => $k->value
                    ];
                }

                Translation::insert($translationsToCreate);


            } catch (\Throwable $th) {
                //throw $th;
            }

            $response['status'] = "success";
            $response['message'] = translate('Language Created Succesfully');
            $response['data'] = $language;
        }
        return $response;
    }

    public function update($request): array
    {
        $language = Language::find($request->input('id'));

        if (!$language) {
            return [
                'status' => 'error',
                'message' => translate('Language Not Found')
            ];
        }

        $country = explode("//", $request->name);
        $code = $request->code ? $request->code : strtolower($country[1]);
        
        if (Language::where('code', $code)->where('id', '!=', $request->input('id'))->exists()) {
            return [
                'status' => 'error',
                'message' => translate('This Language Code Is Already Used !! Try Another')
            ];
        }

        $language->update([
            'name' => $country[0],
            'code' => $language->is_default ? $language->code : $code,
            'ltr' => $request->input('direction'),
            'display_name' => $request->input('display_name')
        ]);

        try {
            Translation::where('code', $language->code)
                ->update(['code' => $code]);
        } catch (\Throwable $th) {
            
        }

        return [
            'status' => 'success',
            'message' => translate('Language Updated Successfully'),
            'data' => $language
        ];
    }



    public function translationVal(string $code): mixed
    {
        $query = Translation::where('code', $code);

        if ($search = request('search')) {
            $query->where('key', 'LIKE', "%{$search}%");
        }

        return $query->paginate(paginateNumber());
    }

    public function translateLang($request): bool
    {

        $response = true;
        try {
            Translation::where('id', $request->data['id'])->update([
                'value' => $request->data['value']
            ]);
            optimize_clear();
        } catch (\Throwable $th) {
            $response = false;
        }

        return $response;

    }

    public function setDefault(int|string $uid): array
    {

        $response['status'] = "success";
        $response['message'] = translate('Default Language Set Successfully');

        Language::where('uid', '!=', $uid)->update([
            'is_default' => (StatusEnum::false)->status(),
            "updated_by" => auth_user('admin')?->id
        ]);
        Language::where('uid', $uid)->update([
            'is_default' => (StatusEnum::true)->status(),
        ]);
        return $response;
    }



    public function destory(int|string $uid): array
    {
        $response['status'] = 'success';
        $response['message'] = translate('Deleted Successfully');
        try {
            $language = Language::where('uid', $uid)->first();
            if ($language->code == 'en' || $language->is_default == StatusEnum::true) {

                $response['code'] = "error";
                $response['message'] = translate('Default & English Language Can Not Be Deleted');
            } else {
                Translation::where("code", $language->code)->delete();
                ModelTranslation::where("locale", $language->code)->delete();
                $language->delete();
                optimize_clear();
            }

        } catch (\Throwable $th) {
            $response['status'] = 'error';
            $response['message'] = translate('Post Data Error !! Can Not Be Deleted');
        }
        return $response;
    }

    public function destoryKey(int|string $id): array
    {
        $response['status'] = 'success';
        $response['message'] = translate('Key Deleted Successfully');
        try {
            $transData = Translation::where('uid', $id)->first();
            $transData->delete();
            optimize_clear();

        } catch (\Throwable $th) {
            $response['status'] = 'error';
            $response['message'] = translate('Post Data Error !! Can Not Be Deleted');
        }
        return $response;
    }


}
