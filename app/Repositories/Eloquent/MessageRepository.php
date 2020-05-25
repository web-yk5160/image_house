<?php

namespace App\Repositories\Eloquent;

use App\Models\Message;
use App\Repositories\Contracts\IMessage;
use Exception;

class MessageRepository extends BaseRepository implements IMessage
{
  public function model()
  {
    return Message::class;
  }
}
