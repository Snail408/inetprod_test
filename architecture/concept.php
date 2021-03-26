<?php
class Concept {
    private $client;

    public function __construct() {
        $this->client = new \GuzzleHttp\Client();
    }

    public function getUserData() {
        $params = [
            'auth' => ['user', 'pass'],
            'token' => $this->getSecretKey()
        ];

        $request = new \Request('GET', 'https://api.method', $params);
        $promise = $this->client->sendAsync($request)->then(function ($response) {
            $result = $response->getBody();
        });

        $promise->wait();
    }

    function getSecretKey(){
        $storage=new keysStorage();
        return $storage->getKey();
    }
}

interface iKeyStorage{
    public function getKey();
    public function setKey($key);
}

class keysStorage implements iKeyStorage{
    private $storage;

    function __constructor(){
        $storageType=config::getInstance()->get('key_storage_type'); // Some singletone config
        $this->loadStorage($storageType);
        return $this;
    }

    private function loadStorage(string $storageType){
        $storageClass=$storageType.'KeyStorage';
        if (!class_exists($storageClass))throw new Exception('Failed to load storage class!');

        $this->storage=new $storageClass;
        if(!$this->storage instanceof iKeyStorage)throw new Exception('Wrong type of storage class!');
    }

    public function getKey(){
        return $this->storage->getKey();
    }

    function setKey($key){}
}

class fileKeyStorage implements iKeyStorage{

    public function getKey(){
        return 'My-File-SuperSecretKey';
    }

    function setKey($key){}
}

class mysqlKeyStorage implements iKeyStorage{

    public function getKey(){
        return 'My-Mysql-SuperSecretKey';
    }

    function setKey($key){}
}

class redisKeyStorage implements iKeyStorage{

    public function getKey(){
        return 'My-Redis-SuperSecretKey';
    }

    function setKey($key){}
}

/*
 * В принципе, как альтернативное решение - типовые субклассы могут расширяться (extends) keysStorage, а не implement от интерфейса. Тогда надо ставить заглушку конструктора и до вызова можно проверять что это класс-наследник. В данном случае такое решение бессмысленно, но в случаях когда из детей надо обращаться друг к другу (зная что они все дети), то это рабочий вариант, при добавлении геттера/сеттера (__get/__set) у родителя.
 */