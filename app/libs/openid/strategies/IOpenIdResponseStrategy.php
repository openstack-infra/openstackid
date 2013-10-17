<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 2:34 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\strategies;

interface IOpenIdResponseStrategy {
    public function handle($response);
}