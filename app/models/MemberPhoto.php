<?php

class MemberPhoto extends Eloquent
{
    protected $table = 'File';
    //external os members db (SS)
    protected $connection = 'os_members';
}