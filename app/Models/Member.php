<?php namespace Models;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

use Auth\AuthHelper;
use Exception;
use Utils\Model\SilverStripeBaseModel;

/**
 * Class Member
 * @package Models
 */
class Member extends SilverStripeBaseModel
{
    protected $table      = 'Member';
    //no timestamps
    public $timestamps     = false;

    /**
     * @param string $password
     * @return bool
     * @throws Exception
     */
    public function checkPassword($password)
    {
        $hash = AuthHelper::encrypt_password($password, $this->Salt, $this->PasswordEncryption);
        $res  = AuthHelper::compare($this->Password, $hash , $this->PasswordEncryption);
        return $res;
    }

    public function groups()
    {
        return $this->belongsToMany('Models\Group', 'Group_Members', 'MemberID', 'GroupID');
    }

    /**
     * @return bool
     */
    public function canLogin()
    {
        return $this->isEmailVerified() && $this->isActive();
    }

    /**
     * @return bool
     */
    public function isActive(){
        $attr = $this->getAttributes();
        if(isset($attr['Active']))
        {
            return (bool)$attr['Active'];
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isEmailVerified()
    {
        $attr = $this->getAttributes();
        if(isset($attr['EmailVerified']))
        {
            return (bool)$attr['EmailVerified'];
        }
        return false;
    }
}