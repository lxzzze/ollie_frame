<?php


namespace core;


use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitterTrait;
use Psr\Http\Message\ResponseInterface;

class Response extends SapiEmitter
{
    use SapiEmitterTrait;
    //重新继承实现SapiEmitter类
    public function emit(ResponseInterface $response): bool
    {
        $this->emitHeaders($response);
        $this->emitStatusLine($response);
        $this->emitBody($response);

        return true;
    }


    private function emitBody(ResponseInterface $response) : void
    {
        echo $response->getBody();
    }
}