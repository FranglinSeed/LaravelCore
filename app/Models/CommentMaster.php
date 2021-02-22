<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentMaster extends Model
{
    protected $table = 'comment_master';

    protected $primaryKey = 'comment_id';

    const CREATED_AT = 'createtime';
    const UPDATED_AT = 'updatetime';
}
