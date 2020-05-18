<?php

namespace App\Http\Controllers\Designs;

use App\Jobs\UploadImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        // requestのバリデーション
        $this->validate($request, [
            'image' => ['required', 'mimes:jpeg,gif,bmp,png', 'max:2048']
        ]);

        // image取得
        $image = $request->file('image');
        $image_path = $image->getPathName();

        // 元のファイル名を取得し、スペースを'_'に置き換える
        // MacBook Air.png = timestamp()_macbook_air.png 同じ名前があるかもしれないからtimestampも含む
        $filename = time()."_".preg_replace('/\s+/', '_', strtolower($image->getClientOriginalName()));

        // temporary location (tmp)にimageを移動
        $tmp = $image->storeAs('uploads/original', $filename, 'tmp');

        // imageのデータベースレコードを作成
        $design = auth()->user()->designs()->create([
            'image' => $filename,
            'disk' => config('site.upload_disk')
        ]);

        // 画像操作を処理するジョブをdispatchする
        $this->dispatch(new UploadImage($design));

        return response()->json($design, 200);
    }

}
