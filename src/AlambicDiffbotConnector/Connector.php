<?php

namespace AlambicDiffbotConnector;
use Swader\Diffbot\Diffbot;


use \Exception;

class Connector
{
    public function __invoke($payload=[])
    {
        if (isset($payload["response"])) {
            return $payload;
        }
        $configs=isset($payload["configs"]) ? $payload["configs"] : [];
        $baseConfig=isset($payload["connectorBaseConfig"]) ? $payload["connectorBaseConfig"] : [];
        if(empty($baseConfig["token"])&&empty($configs["token"])){
            throw new Exception('Token required');
        }
        $token=!empty($configs["token"]) ? $configs["token"] : $baseConfig["token"];
        $diffbot = new Diffbot($token);
        return $payload["isMutation"] ? $this->execute($payload,$diffbot) : $this->resolve($payload,$diffbot);
    }

    public function resolve($payload=[],$diffbot){
        $multivalued=isset($payload["multivalued"]) ? $payload["multivalued"] : false;
        $args=isset($payload["args"]) ? $payload["args"] : [];
        $collection=!empty($configs["collection"]) ? $configs["collection"] : null;
        $result=[];
        throw new Exception('WIP');
        $payload["response"]=$result;
        return $payload;
    }

    public function execute($payload=[],$diffbot){
        throw new Exception('WIP');
    }
}
