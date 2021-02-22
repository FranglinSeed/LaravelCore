<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyMaster extends Model
{
    protected $table = 'company_master';

    protected $primaryKey = 'company_id';

    const CREATED_AT = 'createtime';
    const UPDATED_AT = 'updatetime';
}
