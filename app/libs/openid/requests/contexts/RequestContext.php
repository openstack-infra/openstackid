<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 2:31 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\requests\contexts;


class RequestContext
{

    private $partial_views;
    const StageNull = -1;
    const StageLogin = 0;
    const StageConsent = 1;

    private $stage;

    public function __construct()
    {
        $this->partial_views = array();
        $this->stage = self::StageNull;
    }

    public function addPartialView(PartialView $partial_view)
    {
        $this->partial_views[$partial_view->getName()] = $partial_view;
    }

    public function getPartials()
    {
        return $this->partial_views;
    }

    public function setStage($stage)
    {
        $this->stage = $stage;
    }

    public function getStage()
    {
        return $this->stage;
    }
}