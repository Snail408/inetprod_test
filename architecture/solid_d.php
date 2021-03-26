<?php

interface iHttpService{
    function request($url, $method, $options);
}

class XMLHttpService extends XMLHTTPRequestService implements iHttpService {
    function request($url, $method, $options=[]){
        // Do miracle here:)
    }
}
// С тем же архитектурным успехом XMLHTTPRequestService может принадлежать iHttpService, в зависимости от бизнес-логики и требуемого уровня абстракции.

class Http {
    private $service;

    public function __construct(iHttpService $HttpService) {
        $this->service=$HttpService;
    }

    public function get(string $url, array $options) {
        $this->service->request($url, 'GET', $options);
    }

    public function post(string $url) {
        $this->service->request($url, 'GET');
    }
}

$xmlHttpS=new XMLHttpService();
$http=new Http($xmlHttpS);
$http->get('URL', []);
