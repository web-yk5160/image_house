<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\IUser;
use Exception;

class UserRepository extends BaseRepository implements IUser
{
  public function model()
  {
    return User::class; // 'App\Models\Design'
  }
}
