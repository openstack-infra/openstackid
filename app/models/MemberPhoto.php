<?php

use utils\model\BaseModelEloquent;

class MemberPhoto extends BaseModelEloquent
{
    protected $table = 'File';
    //external os members db (SS)
    protected $connection = 'os_members';
}