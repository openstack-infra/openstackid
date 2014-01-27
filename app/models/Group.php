<?php

/**
 * Class Group
 */
class Group  extends Eloquent {

    protected $table = 'Group';
    //external os members db (SS)
    protected $connection = 'os_members';

    public function members() {
        return $this->belongsToMany('Member', 'Group_Members', 'GroupID', 'MemberID');
    }
}