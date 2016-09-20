<?php

namespace AlambicDiffbotConnector;
use Alambic\Exception\ConnectorConfig;
use Alambic\Exception\ConnectorInternal;
use Swader\Diffbot\Diffbot;



class Connector
{
    public function __invoke($payload=[])
    {
        if (isset($payload["response"])) {
            return $payload;
        }
        $configs=isset($payload["configs"]) ? $payload["configs"] : [];
        $baseConfig=isset($payload["connectorBaseConfig"]) ? $payload["connectorBaseConfig"] : [];
        $collection=!empty($configs["collection"]) ? $configs["collection"] : null;
        if(!$collection&&!empty($baseConfig["collection"])){
            $collection=$baseConfig["collection"];
        }
        if(empty($baseConfig["token"])&&empty($configs["token"])){
            throw new ConnectorConfig('Token required');
        }
        $token=!empty($configs["token"]) ? $configs["token"] : $baseConfig["token"];
        $diffbot = new Diffbot($token);
        return $payload["isMutation"] ? $this->execute($payload,$diffbot,$collection) : $this->resolve($payload,$diffbot,$collection);
    }

    public function resolve($payload=[],$diffbot,$collection = null){
        $multivalued=isset($payload["multivalued"]) ? $payload["multivalued"] : false;
        $args=isset($payload["args"]) ? $payload["args"] : [];
        $isFirstArg=true;
        $query="";
        if(isset($args["search"])){
            $query=$args["search"];
            unset($args["search"]);
            $isFirstArg=false;
        }
        if (!empty($payload['pipelineParams']['orderBy'])) {
            $direction = !empty($payload['pipelineParams']['orderByDirection']) && ($payload['pipelineParams']['orderByDirection'] == 'desc') ? "revsortby" : "sortby";
            $args[$direction]=$payload['pipelineParams']['orderBy'];
        }
        $start = !empty($payload['pipelineParams']['start']) ? $payload['pipelineParams']['start'] : null;
        $limit = !empty($payload['pipelineParams']['limit']) ? $payload['pipelineParams']['limit'] : null;
        if(!$multivalued){
            $limit=1;
        }

        foreach($args as $key=>$value){
            $prefix=" ";
            if($isFirstArg){
                $prefix="";
                $isFirstArg=false;
            }
            if (is_string($value) && $value!="date") {
                $query=$query.$prefix.$key.":\"".$value."\"";
            } else {
                $query=$query.$prefix.$key.":".$value;
            }
        }
        $search = $diffbot->search($query);
        if($collection){
            $search->setCol($collection);
        }
        if($start){
            $search->setStart($start);
        }
        if($limit){
            $search->setNum($limit);
        }
        $searchResult=$search->call();

        $result=[];
        foreach ($searchResult as $article) {
            if(!$multivalued){
                $result=$article->getData();
            } else {
                $result[]=$article->getData();
            }
        }
        $payload["response"]=$result;
        return $payload;
    }

    public function execute($payload=[],$diffbot){
        throw new ConnectorInternal('WIP');
    }
}
