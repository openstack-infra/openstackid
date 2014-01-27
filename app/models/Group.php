<?php

/**
 * Class Group
 */
class Group  extends Eloquent {

    protected $primaryKey ='ID';
    protected $table = 'Group';
    //external os members db (SS)
    protected $connection = 'os_members';

}