<?php
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();
$urlCrm = getenv('URL_CRM');
$apiKey = getenv('API_KEY');

$client = new \RetailCrm\ApiClient(
    $urlCrm,
    $apiKey,
    \RetailCrm\ApiClient::V5
);

// получить список всех клиентов
// записать в отдельный массив все E-mail и идентификатор клиента
// следующим циклом выписать id всех клиентов, у которых E-mail совпадает больше 1 раза
//

// Получаем список всех клиентов
try {
    $response = $client->request->customersList([], null, 20);
} catch (\RetailCrm\Exception\CurlException $e) {
    echo "Connection error: " . $e->getMessage();
}

if ($response->isSuccessful()) {
    $totalPageCount = $response->pagination['totalPageCount'];
    $consolidatedClient = $response->customers[0];
    $clientList = [];
    for ($page = 1; $page <= $totalPageCount; $page++){
        $responseCustomersList = $client->request->customersList(['maxOrdersCount' => 0, 'dateFrom' => '2016-01-01', 'dateTo' => '2017-12-31'], null, 20);
        foreach ($responseCustomersList->customers as $customer) {
            print_r($customer['email']);
            echo PHP_EOL;
            $emailList[$customer['id']] = $customer['email']; // Получаем список клиентов и их E-mail [id]=>[email]
        }
    }
    print_r($emailList);
    echo PHP_EOL;

    //$portions = array_chunk($clientList, 50, true);
    /*foreach ($portions as $portion){
        $responseСustomersCombine = $client->request->customersCombine($portion, $consolidatedClient);
    }*/

    $responseCustomersList2 = $client->request->customersList(['maxOrdersCount' => 0, 'dateFrom' => '2016-01-01', 'dateTo' => '2017-12-31'], $page, 20);
    foreach ($responseCustomersList2->customers as $customer) {
        $emailList = array_map(function ($value) use ($client, $customer) {
            if ($value['email'] == $customer['email']) {
                $responseСustomersCombine = $client->request->customersCombine($customer, $value);
                var_dump($responseСustomersCombine);
            }
        }, $emailList);
    }
} else {
    echo sprintf(
        "Error: [HTTP-code %s] %s",
        $response->getStatusCode(),
        $response->getErrorMsg()
    );
    if (isset($response['errors'])) {
        print_r($response['errors']);
    }
}
