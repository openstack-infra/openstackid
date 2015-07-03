<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
/**
 * Defines application features from the specific context.
 */
class FeatureContext extends LaravelContext
{
    private $params = array();

    private $response;

    public function __construct(){
        parent::__construct();
    }

    /**
     * @Given Authorization Request is Fill With:
     */
    public function authorizationRequestIsFillWith(TableNode $table)
    {
        $hash = $table->getHash();
        foreach ($hash as $row) {
            $this->params[$row['param']] = $row['value'];
            if($row['param'] == 'scope')
                $this->params[$row['param']] = sprintf($this->params[$row['param']], $this->current_realm);
        }
    }

    /**
     * @Then I get a :arg1 Response
     */
    public function iGetAResponse($arg1)
    {
        return $this->response->getStatusCode() == intval($arg1);
    }

    /**
     * @When I Request :arg1 And :arg2
     */
    public function iRequestAnd($method, $action)
    {
        $this->response = $this->action($method, $action,
            $this->params,
            array(),
            array(),
            array());
    }

    /**
     * @Then I Should Navigate to :arg1
     */
    public function iShouldNavigateTo($url)
    {
        $url = sprintf($url, $this->current_realm);
        $this->visitPath($url);
    }



}