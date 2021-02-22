<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyMaster extends Model
{
    protected $table = 'survey_master';

    protected $primaryKey = 'survey_id';

    const CREATED_AT = 'createtime';
    const UPDATED_AT = 'updatetime';
}
