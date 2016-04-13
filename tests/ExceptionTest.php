<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 11/8/13
 * Time: 1:21 PM
 */
use openid\exceptions\ReplayAttackException;
class ExceptionTest extends TestCase{

    private function getExName(\Exception $ex){
        return get_class($ex);
    }

    public function testExceptionTypes(){
        $ex1 = new ReplayAttackException();
        $class_name = $this->getExName($ex1);
        $this->assertTrue($class_name == 'openid\exceptions\ReplayAttackException');
    }
} 