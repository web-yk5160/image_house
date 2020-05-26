<?php

namespace App\Repositories\Eloquent;

use App\Models\Design;
use App\Repositories\Contracts\IDesign;
use Illuminate\Http\Request;

class DesignRepository extends BaseRepository implements IDesign
{
  public function model()
  {
    return Design::class; // 'App\Models\Design'
  }

  public function applyTags($id, array $data)
  {
    $design = $this->find($id);
    $design->retag($data);
  }

  public function addComment($designId, array $data)
  {
    // コメントを作成したいデザインを取得する
    $design = $this->find($designId);

    // デザインのコメントを作成する
    $comment = $design->comments()->create($data);

    return $comment;
  }

  public function like($id)
  {
    $design = $this->model->findOrFail($id);
    if ($design->isLikedByUser(auth()->id())) {
      $design->unlike();
    }  else {
      $design->like();
    }
  }

  public function isLikedByUser($id)
  {
    $design = $this->model->findOrFail($id);
    return $design->isLikedByUser(auth()->id());
  }

  public function search(Request $request)
  {
    $query = (new $this->model)->newQuery();
    $query->where('is_live', true);

    // comment付きのdesignのみを返す
    if($request->has_comments) {
      $query->has('comments');
    }

    // teamに所属しているdesignのみを返す
    if($request->has_team) {
      $query->has('team');
    }

    if($request->q) {
      $query->where(function($q) use ($request) {
        $q->where('title', 'like', '%'.$request->q.'%')
            ->orWhere('description', 'like', '%'.$request->q.'%');
      });
    }

    // likesまたは最新でqueryを並べ替え
    if($request->orderBy=='likes') {
      $query->withCount('likes') //likes_count
          ->orderByDesc('likes_count');
    } else {
      $query->latest();
    }

    return $query->get();
  }
}
