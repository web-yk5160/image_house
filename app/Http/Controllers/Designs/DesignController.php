<?php

namespace App\Http\Controllers\Designs;

use App\Models\Design;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DesignResource;
use Illuminate\Support\Facades\Storage;

class DesignController extends Controller
{
    public function update(Request $request, $id)
    {
        $design = Design::findOrFail($id);
        $this->authorize('update', $design);
        $this->validate($request, [
            'title' => ['required', 'unique:designs,title,'.$id],
            'description' => ['required', 'string', 'min:20', 'max:140'],
            'tags' => ['required']
        ]);

        $design->update([
            'title' => $request->title,
            'description' => $request->description,
            'slug' => Str::slug($request->title), // hello world hello-world
            'is_live' => ! $design->upload_successful ? false : $request->is_live
        ]);

        // タグを付ける
        $design->retag($request->tags);

        // return response()->json($design, 200);
        return new DesignResource($design);
    }

    public function destroy($id)
    {
        $design = Design::findOrFail($id);
        $this->authorize('delete', $design);

        // レコードに関連付けられているファイルを削除する
        foreach(['thumbnail', 'large', 'original'] as $size) {
            // ファイルがDBにあるかチェックする
            if (Storage::disk($design->disk)->exists("uploads/designs/{$size}/".$design->image)) {
                    Storage::disk($design->disk)->delete("uploads/designs/{$size}/".$design->image);
            }
        }

        $design->delete();

        return response()->json(['message' => '削除しました'], 200);
    }
}
