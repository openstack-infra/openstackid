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
     * @Given these OAuth2 parameters:
     */
    public function theseOauthParameters(TableNode $table)
    {
        $hash = $table->getHash();
        foreach ($hash as $row) {
            $this->params[$row['param']] = $row['value'];
            if($row['param'] == 'scope')
                $this->params[$row['param']] = sprintf($this->params[$row['param']], $this->current_realm);
        }
    }

    /**
     * @Given exits client Id :arg1
     */
    public function exitsClientId($client_id)
    {
        $client = Client::where('client_id', '=', $client_id)->first();
        if(is_null($client))
            throw new Exception(sprintf('client id %s does not exist', $client_id));
    }

    /**
     * @Given navigate to controller action :arg1 with HTTP method :arg2
     */
    public function navigateToControllerActionWithHttpMethod($action, $method)
    {
        $this->response = $this->action($method, $action,
            $this->params,
            array(),
            array(),
            array());
    }

    /**
     * @When i get log as user :arg1 using password :arg2
     */
    public function iGetLogAsUserUsingPassword($user, $password)
    {

        Session::save();

        $login_url =  $this->response->getTargetUrl();

        $this->response = $this->call('POST', $login_url, array(
            'username'  => $user,
            'password'  => $password,
            '_token' => Session::token()
        ));

    }

    /**
     * @When allow consent :arg1
     */
    public function allowConsent($arg1)
    {
        Session::save();

        $auth_url =  $this->response->getTargetUrl();

        $this->response = $this->call('GET', $auth_url);

        $consent_url =  $this->response->getTargetUrl();

        $this->response = $this->call('POST', $consent_url, array(
            'trust'  => $arg1,
            '_token' => Session::token()
        ));
    }

    /**
     * @Then i get a valid Auth code
     */
    public function iGetAValidAuthCode()
    {
        $response_url = $this->response->getTargetUrl();

        $this->response = $this->call("GET", $response_url);

        $response_url = $this->response->getTargetUrl();
        $comps = @parse_url($response_url);
        $query = $comps['query'];
        $output = array();
        parse_str($query, $output);
        $this->assertTrue(array_key_exists('code', $output) );
        $this->assertTrue(!empty($output['code']) );
        echo $output['code'];
    }

}