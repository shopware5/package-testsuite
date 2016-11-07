<?php

namespace Shopware\Helper;

use Faker\Factory as FakerFactory;
use GuzzleHttp\Client as Guzzle;
use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Exception\CategoryPathNotStartingAtRootException;
use Shopware\Exception\MissingRequirementException;
use Shopware\Exception\MultipleChoicesException;
use Shopware\Exception\NoArticleNameException;

class ApiClient
{
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_POST = 'POST';
    const METHOD_DELETE = 'DELETE';
    private $validMethods = array(
        self::METHOD_GET,
        self::METHOD_PUT,
        self::METHOD_POST,
        self::METHOD_DELETE
    );
    private $apiUrl;
    private $assetUrl;
    private $auth;
    /** @var \Faker\Generator */
    private $faker;

    public function __construct($apiUrl, $assetUrl, $username, $apiKey)
    {
        $this->apiUrl = rtrim($apiUrl, '/') . '/';
        $this->assetUrl = rtrim($assetUrl, '/') . '/';
        $this->client = new Guzzle();

        if (empty($username)) {
            throw new MissingRequirementException('Missing API username.');
        }

        if (empty($apiKey)) {
            throw new MissingRequirementException('Missing API password.');
        }

        $this->auth = [$username, $apiKey, 'digest'];

        $this->faker = FakerFactory::create('de_DE');
    }

    public function call($url, $method = self::METHOD_GET, $data = array(), $params = array())
    {
        if (!in_array($method, $this->validMethods)) {
            throw new \Exception('Invalid HTTP-Method: ' . $method);
        }
        $queryString = '';
        if (!empty($params)) {
            $queryString = http_build_query($params);
        }
        $url = rtrim($url, '?') . '?';
        $url = $this->apiUrl . $url . $queryString;
        $url = rtrim($url, '?');

        $data['auth'] = $this->auth;
        $data['debug'] = false;

        $response = $this->client->request($method, $url, $data);
        $httpCode = $response->getStatusCode();
        $result = $response->getBody()->getContents();

        return $this->prepareResponse($result, $httpCode);
    }

    public function get($url, $params = array())
    {
        return $this->call($url, self::METHOD_GET, array(), $params);
    }

    public function post($url, $data = array(), $params = array())
    {
        return $this->call($url, self::METHOD_POST, ['json' => $data], $params);
    }

    public function put($url, $data = array(), $params = array())
    {
        return $this->call($url, self::METHOD_PUT, $data, $params);
    }

    public function delete($url, $params = array())
    {
        return $this->call($url, self::METHOD_DELETE, array(), $params);
    }

    protected function prepareResponse($result, $httpCode)
    {
        if (null === $decodedResult = json_decode($result, true)) {
            $jsonErrors = array(
                JSON_ERROR_NONE => 'No error occurred',
                JSON_ERROR_DEPTH => 'The maximum stack depth has been reached',
                JSON_ERROR_CTRL_CHAR => 'Control character issue, maybe wrong encoded',
                JSON_ERROR_SYNTAX => 'Syntaxerror',
            );
            $error = "<h2>Could not decode json</h2>";
            $error .= "json_last_error: " . $jsonErrors[json_last_error()];
            $error .= "<br>Raw:<br>";
            $error .= "<pre>" . print_r($result, true) . "</pre>";
            throw new \Exception($error);
        }
        if (!isset($decodedResult['success'])) {
            throw new \Exception("Invalid Response");
        }
        if (!$decodedResult['success']) {
            $error = "<h2>No Success</h2>";
            $error .= "<p>" . $decodedResult['message'] . "</p>";
            throw new \Exception($error);
        }
        return $decodedResult;
    }

    /**
     * @param array $array
     * @param array $expectedFields
     * @throws \Exception
     */
    private function throwExceptionWhenEmpty(array $array, array $expectedFields)
    {
        foreach ($expectedFields as $field) {
            if (empty($array[$field])) {
                throw new \Exception("Field $field is required.");
            }
        }
    }

    /**
     * @param string $name
     * @return bool
     * @throws \Exception
     */
    public function articleExistsByName($name)
    {
        $response = $this->get('api/articles', [
            'filter' => [
                'name' => (string)$name,
            ],
            'limit' => 1,
        ]);

        if ($response['success'] !== true) {
            throw new \Exception('API communication unsuccessful: ' . print_r($response, true));
        }

        if ($response['total'] > 0) {
            return true;
        }

        return false;
    }

    public function createArticle($product)
    {
        $categories = $product['categories'];
        $categoryIds = [];
        if (!empty($categories)) {
            $categoryIds = $this->createCategoryTrees($categories);
        }
        $this->post('api/articles', $this->buildArticleDataArray($product, $categoryIds));
    }

    /**
     * @param string $categoryTrees Trees delimited by ; and Categories delimited by >
     * @return array
     */
    public function createCategoryTrees($categoryTrees)
    {
        $categoryTrees = explode(';', $categoryTrees);
        $categoryIds = [];
        foreach ($categoryTrees as $categoryTree) {
            $categoryIds[] = $this->createCategoryTree(trim($categoryTree));
        }
        return $categoryIds;
    }

    public function createCategoryTree($categories)
    {
        $categories = explode('>', $categories);
        $rootCategory = trim(array_shift($categories));
        if ($rootCategory !== "Root") {
            throw new CategoryPathNotStartingAtRootException("The first element of a category path has to be Root.");
        }

        $parentId = 1;

        foreach ($categories as $category) {
            $category = trim($category);
            $existsResponse = $this->get('api/categories', [
                'filter' => [
                    'name' => $category,
                    'parentId' => $parentId,
                ]
            ]);

            if ($existsResponse['total'] === 0) {
                $categoryData = $this->createCategory($category, $parentId);
                $parentId = $categoryData['data']['id'];
                continue;
            }

            if ($existsResponse['total'] > 1) {
                throw new MultipleChoicesException(sprintf('There are multiple definitions for "%s" with parent id %i.',
                    $category, $parentId));
            }

            $parentId = $existsResponse['data'][0]['id'];
        }
        return $parentId;
    }

    /**
     * @param string $name
     * @param int $parentId
     * @return array
     */
    private function createCategory($name, $parentId)
    {
        return $this->post('api/categories', [
            'parentId' => $parentId,
            'name' => $name,
        ]);
    }

    /**
     * @param array $product
     * @param array $categories
     * @return array
     * @throws NoArticleNameException
     */
    private function buildArticleDataArray(array $product, array $categories)
    {
        $this->throwExceptionWhenEmpty($product, ['name']);

        $faker = $this->faker;

        $articleData = [
            'name' => $product['name'],
            'active' => array_key_exists('active', $product) ? $product['active'] : true,
            'description' => array_key_exists('description',
                $product) ? $product['description'] : $faker->realText($faker->numberBetween(10, 20)),
            'description_long' => array_key_exists('description_long',
                $product) ? $product['description_long'] : $faker->realText($faker->numberBetween(50, 200)),
            'tax' => array_key_exists('tax', $product) ? $product['tax'] : 19,
            'supplier' => array_key_exists('supplier', $product) ? $product['supplier'] : 'NoName GmbH',
            'priceGroupId' => array_key_exists('priceGroupId', $product) ? $product['priceGroupId'] : 1,

            'images' => [
                ['link' => $this->assetUrl . '800x600/' . urlencode($product['name']) . '.jpg'],
            ],

            'mainDetail' => [
                'number' => array_key_exists('number', $product) ? $product['number'] : 'swTEST' . uniqid(),
                'inStock' => array_key_exists('inStock', $product) ? $product['inStock'] : 16,
                'active' => array_key_exists('active', $product) ? $product['active'] : true,
                'prices' => [
                    [
                        'customerGroupKey' => array_key_exists('customerGroupKey',
                            $product) ? $product['customerGroupKey'] : 'EK',
                        'price' => array_key_exists('price', $product) ? $product['price'] : 99.34,
                    ],
                ]
            ],
        ];
        if (!empty($categories)) {
            $articleData['categories'] = [];
            foreach ($categories as $category) {
                $articleData['categories'][] = ['id' => $category];
            }
        }
        return $articleData;
    }

    /**
     * @param string $email
     * @return bool
     */
    public function customerExists($email)
    {
        $response = $this->get('api/customers', [
            'filter' => [
                'email' => (string)$email,
            ],
            'limit' => 1,
        ]);
        return $response['total'] > 0;
    }

    public function createCustomer(array $customer)
    {
        $this->throwExceptionWhenEmpty($customer, ['email', 'password']);
        $this->post('api/customers', $this->buildCustomerDataArray($customer));
    }

    /**
     * @param array $customer
     * @return array
     */
    private function buildCustomerDataArray(array $customer)
    {
        $faker = $this->faker;
        $birthday = $faker->dateTimeBetween('-70 years', '-18 years')->format($faker->iso8601);
        return [
            'paymentId' => array_key_exists('paymentId', $customer) ? $customer['paymentId'] : 5,
            'groupKey' => array_key_exists('groupKey', $customer) ? $customer['groupKey'] : 'EK',
            'shopId' => array_key_exists('shopId', $customer) ? $customer['shopId'] : 1,
            'priceGroupId' => array_key_exists('priceGroupId', $customer) ? $customer['active'] : null,
            'encoderName' => array_key_exists('encoderName', $customer) ? $customer['encoderName'] : 'bcrypt',
            'password' => $customer['password'],
            'active' => array_key_exists('active', $customer) ? $customer['active'] : true,
            'email' => $customer['email'],
            'paymentPreset' => array_key_exists('paymentPreset', $customer) ? $customer['paymentPreset'] : 5,
            'languageId' => array_key_exists('languageId', $customer) ? $customer['languageId'] : '1',
            'salutation' => array_key_exists('salutation', $customer) ? $customer['salutation'] : 'mr',
            'title' => array_key_exists('title', $customer) ? $customer['title'] : '',
            'firstname' => array_key_exists('firstname', $customer) ? $customer['firstname'] : $faker->firstName,
            'lastname' => array_key_exists('lastname', $customer) ? $customer['lastname'] : $faker->lastName,
            'birthday' => array_key_exists('birthday', $customer) ? $customer['birthday'] : $birthday,
            'country' => array_key_exists('country', $customer) ? $customer['country'] : 2,
            'street' => array_key_exists('street', $customer) ? $customer['street'] : $faker->streetName . ' ' . rand(1,
                    101),
            'city' => array_key_exists('city', $customer) ? $customer['city'] : $faker->city,
            'zipcode' => array_key_exists('zip', $customer) ? $customer['zip'] : rand(11111, 99999),
            'billing' => [
                'salutation' => array_key_exists('salutation', $customer) ? $customer['salutation'] : 'mr',
                'title' => array_key_exists('title', $customer) ? $customer['title'] : '',
                'firstname' => array_key_exists('firstname', $customer) ? $customer['firstname'] : $faker->firstName,
                'lastname' => array_key_exists('lastname', $customer) ? $customer['lastname'] : $faker->lastName,
                'birthday' => array_key_exists('birthday', $customer) ? $customer['birthday'] : $birthday,
                'street' => array_key_exists('street',
                    $customer) ? $customer['street'] : $faker->streetName . ' ' . rand(1, 101),
                'city' => array_key_exists('city', $customer) ? $customer['city'] : $faker->city,
                'zipcode' => array_key_exists('zip', $customer) ? $customer['zip'] : rand(11111, 99999),
                'country' => array_key_exists('country', $customer) ? $customer['country'] : 2,
            ]
        ];
    }

    public function setCountryData(array $data)
    {
        $countries = [];

        foreach ($data as $country) {
            $iso = strtoupper($country['iso']);
            $countries[$iso] = $country;
        }

        $countryCount = count($countries);
        $countriesFound = 0;

        $countrylist = $this->get('api/countries');
        foreach ($countrylist['data'] as $country) {
            if (array_key_exists($country['iso'], $countries)) {
                $countryData = $countries[$country['iso']];
                $data = [
                    'name' => $country['name'],
                    'iso' => $country['iso'],
                    'iso3'=> $country['iso3'],
                    'isoName'=> $country['isoName'],
                    'shippingFree' => (bool)(array_key_exists('shippingFree', $countryData) ? $countryData['shippingFree'] : false),
                    'taxFree' => (bool)(array_key_exists('taxFree', $countryData) ? $countryData['taxFree'] : false),
                    'taxFreeUstId' => (bool)(array_key_exists('taxFreeUstId', $countryData) ? $countryData['taxFreeUstId'] : false),
                    'taxFreeUstIdChecked' => (bool)(array_key_exists('taxFreeUstIdChecked', $countryData) ? $countryData['taxFreeUstIdChecked'] : false),
                    'active' => (bool)(array_key_exists('active', $countryData) ? $countryData['active'] : true),
                    'displayStateInRegistration' => (bool)(array_key_exists('displayStateInRegistration', $countryData) ? $countryData['displayStateInRegistration'] : true),
                    'forceStateInRegistration' => (bool)(array_key_exists('forceStateInRegistration', $countryData) ? $countryData['forceStateInRegistration'] : false),
                ];

                $this->put('api/countries/'.$country['id'], ['json' => $data]);
                $countriesFound++;
            }
            if ($countryCount == $countriesFound) {
                break;
            }
        }
    }

    public function customerGroupExistsByKey($key)
    {
        $id = $this->getCustomerGroupIdByKey($key);

        if ($id === null) {
            return false;
        }

        return true;
    }

    /**
     * @param string $key
     * @return int|null
     * @throws \Exception
     */
    private function getCustomerGroupIdByKey($key)
    {
        $response = $this->get('api/customerGroups', [
            'filter' => [
                'key' => (string)$key,
            ],
            'limit' => 1,
        ]);

        if ($response['success'] !== true) {
            throw new \Exception('API communication unsuccessful: ' . print_r($response, true));
        }

        if ($response['total'] === 0) {
            return null;
        }

        return (int)$response['data'][0]['id'];
    }

    public function createCustomerGroup($customerGroup)
    {
        $this->throwExceptionWhenEmpty($customerGroup, ['key', 'name']);
        $this->post('api/customerGroups', $this->buildCustomerGroupDataArray($customerGroup));
    }

    private function buildCustomerGroupDataArray(array $group)
    {
        return [
            'key' => $group['key'],
            'name' => $group['name'],
            'tax' => array_key_exists('tax', $group) ? $group['tax'] : true,
            'taxInput' => array_key_exists('taxInput', $group) ? $group['taxInput'] : true,
            'mode' => array_key_exists('mode', $group) ? $group['mode'] : false,
            'discount' => array_key_exists('discount', $group) ? $group['discount'] : 0,
            'minimumOrder' => array_key_exists('minimumOrder', $group) ? $group['minimumOrder'] : 10,
            'minimumOrderSurcharge' => array_key_exists('minimumOrderSurcharge', $group) ? $group['minimumOrderSurcharge'] : 5,
        ];
    }

    public function updateCustomerGroup($customerGroup)
    {
        $this->throwExceptionWhenEmpty($customerGroup, ['key', 'name']);

        $id = $this->getCustomerGroupIdByKey($customerGroup['key']);

        $data = $this->buildCustomerGroupDataArray($customerGroup);
        $this->put('api/customerGroups/'.$id, ['json' => $data]);
    }

    /**
     * Deletes the user account associated with the given e-mail address
     *
     * @param string $email
     */
    public function deleteCustomerByEmail($email)
    {
        $response = $this->get('api/customers', [
            'filter' => [
                'email' => (string)$email,
            ],
            'limit' => 1,
        ]);
        if ($response['total'] == 0) {
            return;
        }
        $this->deleteCustomerById((int)$response['data'][0]['id']);
    }

    /**
     * Deletes the user account with the given id
     *
     * @param int $id
     */
    public function deleteCustomerById($id)
    {
        $this->delete('api/customers/'.$id);
    }
}
