<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 2:28 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\XRDS;

/*
    XRDS Service Element
*/
class XRDSService {

    private $priority;
    private $type;
    private $uri;
    private $local_id;
    private $extensions;

    public function __construct($priority, $type, $uri,$extensions=array(), $local_id=null){
        $this->priority=$priority;
        $this->type=$type;
        $this->uri=$uri;
        $this->local_id=$local_id;
        $this->extensions = $extensions;
    }

    public function render(){
      $local_id =empty($this->local_id)?"":"<LocalID>{$this->local_id}</LocalID>";

      $extensions ="";
      foreach($this->extensions as $extension){
          $extensions.="<Type>{$extension}</Type>";
      }

      $element = <<< SERVICE
      <Service priority="{$this->priority}">
          <Type>{$this->type}</Type>
          {$extensions}
          <URI>{$this->uri}</URI>
          {$local_id}
      </Service>
SERVICE;
        return $element;
    }

    public function getPriority(){
        return $this->priority;
    }
    public function getType(){
        return $this->type;
    }
    public function getUri(){
        return $this->uri;
    }
    public function getLocalId(){
        return $this->local_id;
    }
}