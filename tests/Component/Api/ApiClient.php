<?php

declare(strict_types=1);

namespace Shopware\Component\Api;

use Exception;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Client as Guzzle;
use RuntimeException;

class ApiClient
{
    /**
     * Supported HTTP methods
     */
    public const METHOD_GET = 'GET';
    public const METHOD_PUT = 'PUT';
    public const METHOD_POST = 'POST';
    public const METHOD_DELETE = 'DELETE';

    private array $validMethods = [
        self::METHOD_GET,
        self::METHOD_PUT,
        self::METHOD_POST,
        self::METHOD_DELETE,
    ];

    private string $apiUrl;

    private string $assetUrl;

    private array $auth;

    private Generator $faker;

    private Guzzle $client;

    public function __construct(string $apiUrl, string $assetUrl, string $username, string $apiKey)
    {
        $this->apiUrl = rtrim($apiUrl, '/') . '/';
        $this->assetUrl = rtrim($assetUrl, '/') . '/';
        $this->client = new Guzzle();

        if (empty($username)) {
            throw new RuntimeException('Missing API username.');
        }

        if (empty($apiKey)) {
            throw new RuntimeException('Missing API password.');
        }

        $this->auth = [$username, $apiKey, 'basic'];

        $this->faker = FakerFactory::create('de_DE');
    }

    /**
     * Perform GET request on API
     */
    public function get(string $url, array $params = []): array
    {
        return $this->call($url, self::METHOD_GET, [], $params);
    }

    /**
     * Perform POST request on API
     */
    public function post(string $url, array $data = [], array $params = []): array
    {
        return $this->call($url, self::METHOD_POST, ['json' => $data], $params);
    }

    /**
     * Perform PUT request on API
     */
    public function put(string $url, array $data = [], array $params = []): array
    {
        return $this->call($url, self::METHOD_PUT, $data, $params);
    }

    /**
     * Perform DELETE request on API
     */
    public function delete(string $url, array $params = []): array
    {
        return $this->call($url, self::METHOD_DELETE, [], $params);
    }

    /**
     * @throws Exception
     */
    public function articleExistsByName(string $name): bool
    {
        $response = $this->get('api/articles', [
            'filter' => [
                'name' => $name,
            ],
            'limit' => 1,
        ]);

        return $response['total'] > 0;
    }

    /**
     * Create new product in shop
     *
     * @return int the ID of the created product
     */
    public function createArticle(array $product): int
    {
        $categories = $product['categories'];
        $categoryIds = [];
        if (!empty($categories)) {
            $categoryIds = $this->createCategoryTrees($categories);
        }

        $response = $this->post('api/articles', $this->buildArticleDataArray($product, $categoryIds));

        return (int) $response['data']['id'];
    }

    /**
     * @param string $categoryTrees Trees delimited by ; and Categories delimited by >
     */
    public function createCategoryTrees(string $categoryTrees): array
    {
        $categoryTreesArray = explode(';', $categoryTrees);
        $categoryIds = [];
        foreach ($categoryTreesArray as $categoryTree) {
            $categoryIds[] = $this->createCategoryTree(trim($categoryTree));
        }

        return $categoryIds;
    }

    /**
     * @throws RuntimeException
     */
    public function createCategoryTree(string $categories): int
    {
        $categoriesArray = explode('>', $categories);
        $rootCategory = trim(array_shift($categoriesArray));
        if ($rootCategory !== 'Root') {
            throw new RuntimeException('The first element of a category path has to be Root.');
        }

        $parentId = 1;

        foreach ($categoriesArray as $category) {
            $category = trim($category);
            $existsResponse = $this->get('api/categories', [
                'filter' => [
                    'name' => $category,
                    'parentId' => $parentId,
                ],
            ]);

            if ($existsResponse['total'] === 0) {
                $categoryData = $this->createCategory($category, $parentId);
                $parentId = $categoryData['data']['id'];
                continue;
            }

            if ($existsResponse['total'] > 1) {
                throw new RuntimeException(\sprintf('There are multiple definitions for "%s" with parent id %s.', $category, $parentId));
            }

            $parentId = $existsResponse['data'][0]['id'];
        }

        return $parentId;
    }

    public function customerExists(string $email): bool
    {
        $response = $this->get('api/customers', [
            'filter' => [
                'email' => $email,
            ],
            'limit' => 1,
        ]);

        return $response['total'] > 0;
    }

    /**
     * @return int ID of the customer that was created
     */
    public function createCustomer(array $customer): int
    {
        $this->throwExceptionWhenEmpty($customer, ['email', 'password']);
        $response = $this->post('api/customers', $this->buildCustomerDataArray($customer));

        return $response['data']['id'];
    }

    /**
     * Get the internal country id for a given ISO
     *
     * @throws Exception
     */
    public function getCountryIdByISO(string $iso): int
    {
        foreach ($this->get('api/countries')['data'] as $country) {
            if (\array_key_exists('iso', $country) && $country['iso'] === $iso) {
                return $country['id'];
            }
        }

        throw new Exception('Could not find country by ISO ' . $iso);
    }

    public function setCountryData(array $data): void
    {
        $countries = [];

        foreach ($data as $country) {
            $iso = strtoupper($country['iso']);
            $countries[$iso] = $country;
        }

        $countryCount = \count($countries);
        $countriesFound = 0;

        $countrylist = $this->get('api/countries');
        foreach ($countrylist['data'] as $country) {
            if (\array_key_exists($country['iso'], $countries)) {
                $countryData = $countries[$country['iso']];
                $data = [
                    'name' => $country['name'],
                    'iso' => $country['iso'],
                    'iso3' => $country['iso3'],
                    'isoName' => $country['isoName'],
                    'shippingFree' => (bool) (\array_key_exists('shippingFree',
                        $countryData) ? $countryData['shippingFree'] : false),
                    'taxFree' => (bool) (\array_key_exists('taxFree',
                        $countryData) ? $countryData['taxFree'] : false),
                    'taxFreeUstId' => (bool) (\array_key_exists('taxFreeUstId',
                        $countryData) ? $countryData['taxFreeUstId'] : false),
                    'taxFreeUstIdChecked' => (bool) (\array_key_exists('taxFreeUstIdChecked',
                        $countryData) ? $countryData['taxFreeUstIdChecked'] : false),
                    'active' => (bool) (\array_key_exists('active',
                        $countryData) ? $countryData['active'] : true),
                    'displayStateInRegistration' => (bool) (\array_key_exists('displayStateInRegistration',
                        $countryData) ? $countryData['displayStateInRegistration'] : true),
                    'forceStateInRegistration' => (bool) (\array_key_exists('forceStateInRegistration',
                        $countryData) ? $countryData['forceStateInRegistration'] : false),
                ];

                $this->put('api/countries/' . $country['id'], ['json' => $data]);
                ++$countriesFound;
            }
            if ($countryCount === $countriesFound) {
                break;
            }
        }
    }

    /**
     * Create a new order
     */
    public function createOrder(array $orderData): void
    {
        if ($this->customerExists($orderData['customer.email'])) {
            $this->deleteCustomerByEmail($orderData['customer.email']);
        }

        $customerId = $this->createCustomer(['email' => $orderData['customer.email'], 'password' => 'shopware']);

        $articleId = $this->createArticle([
            'name' => $orderData['position.name'],
            'price' => $orderData['position.price'],
            'categories' => [],
        ]);

        $countryId = \array_key_exists('shipping.country', $orderData)
            ? $this->getCountryIdByISO($orderData['shipping.country'])
            : 2;

        $this->post('api/orders', [
            'customerId' => $customerId,
            'paymentId' => 5, // "Vorkasse"
            'dispatchId' => 9, // "Standard Versand"
            'partnerId' => '',
            'shopId' => 1,
            'invoiceAmount' => $orderData['position.price'] * $orderData['position.quantity'],
            'invoiceAmountNet' => $orderData['position.price'] * $orderData['position.quantity'] / 119 * 100,
            'invoiceShipping' => 0,
            'invoiceShippingNet' => 0,
            'orderTime' => '2012-08-31 08:51:46',
            'net' => 0,
            'taxFree' => 0,
            'languageIso' => '1',
            'currency' => 'EUR',
            'currencyFactor' => 1,
            'remoteAddress' => '217.86.205.141',
            'details' => [
                [
                    'articleId' => $articleId,
                    'taxId' => 1,
                    'taxRate' => 19,
                    'statusId' => 0,
                    'articleNumber' => 'BOT001',
                    'price' => $orderData['position.price'],
                    'quantity' => $orderData['position.quantity'],
                    'articleName' => $orderData['position.name'],
                    'shipped' => 0,
                    'shippedGroup' => 0,
                    'mode' => 0,
                    'esdArticle' => 0,
                ],
            ],
            'documents' => [],
            'billing' => [
                'id' => 2,
                'customerId' => 1,
                'countryId' => $countryId,
                'stateId' => 3,
                'company' => 'shopware AG',
                'salutation' => 'mr',
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'street' => 'Mustermannstr. 92',
                'zipCode' => '48624',
                'city' => 'Schuppingen',
            ],
            'shipping' => [
                'id' => 2,
                'countryId' => $countryId,
                'stateId' => 3,
                'customerId' => 1,
                'company' => 'shopware AG',
                'salutation' => 'mr',
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'street' => 'Mustermannstr 92',
                'zipCode' => '48624',
                'city' => 'Schuppingen',
            ],
            'paymentStatusId' => 17,
            'orderStatusId' => 0,
        ]);
    }

    public function customerGroupExistsByKey($key): bool
    {
        $id = $this->getCustomerGroupIdByKey($key);

        return $id !== null;
    }

    public function createCustomerGroup(array $customerGroup): void
    {
        $this->throwExceptionWhenEmpty($customerGroup, ['key', 'name']);
        $this->post('api/customerGroups', $this->buildCustomerGroupDataArray($customerGroup));
    }

    public function updateCustomerGroup(array $customerGroup): void
    {
        $this->throwExceptionWhenEmpty($customerGroup, ['key', 'name']);

        $id = $this->getCustomerGroupIdByKey($customerGroup['key']);

        $data = $this->buildCustomerGroupDataArray($customerGroup);
        $this->put('api/customerGroups/' . $id, ['json' => $data]);
    }

    /**
     * Deletes the user account associated with the given e-mail address
     */
    public function deleteCustomerByEmail(string $email): void
    {
        $response = $this->get('api/customers', [
            'filter' => [
                'email' => $email,
            ],
            'limit' => 1,
        ]);
        if ((int) $response['total'] === 0) {
            return;
        }
        $this->deleteCustomerById((int) $response['data'][0]['id']);
    }

    /**
     * Helper function that deletes all existing customers
     */
    public function deleteAllCustomers(): void
    {
        $response = $this->get('api/customers');
        foreach ($response['data'] as $customer) {
            $this->deleteCustomerById($customer['id']);
        }
    }

    /**
     * Deletes the user account with the given id
     */
    public function deleteCustomerById(int $id): void
    {
        $this->delete('api/customers/' . $id);
    }

    /**
     * @param int $retries Number of times the request is repeated if it was unsuccessful
     *
     * @throws Exception
     */
    public function call(string $url, string $method = self::METHOD_GET, array $data = [], array $params = [], int $retries = 1): array
    {
        if (!\in_array($method, $this->validMethods, true)) {
            throw new Exception('Invalid HTTP-Method: ' . $method);
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

        // Retry API requests for enhanced test stability
        do {
            $response = $this->client->request($method, $url, $data);
            $statusCode = $response->getStatusCode();
            --$retries;
        } while ($statusCode !== 200 && $retries > 0);

        $result = $response->getBody()->getContents();

        return $this->prepareResponse($result);
    }

    /**
     * @throws Exception
     */
    public function createProperty(array $property): void
    {
        $propertiesArray = [
            'name' => $property['set'],
            'position' => 1,
            'comparable' => 1,
            'sortmode' => 1,
        ];
        $this->post('api/propertyGroups', $propertiesArray);

        $this->throwExceptionWhenEmpty($property, ['key', 'set', 'group', 'option']);

        $this->post('api/articles', $this->buildPropertyDataArray($property));
    }

    /**
     * @throws Exception
     */
    protected function prepareResponse(string $result): array
    {
        if (null === $decodedResult = json_decode($result, true)) {
            $jsonErrors = [
                JSON_ERROR_NONE => 'No error occurred',
                JSON_ERROR_DEPTH => 'The maximum stack depth has been reached',
                JSON_ERROR_CTRL_CHAR => 'Control character issue, maybe wrong encoded',
                JSON_ERROR_SYNTAX => 'Syntaxerror',
            ];
            $error = '<h2>Could not decode json</h2>';
            $error .= 'json_last_error: ' . $jsonErrors[json_last_error()];
            $error .= '<br>Raw:<br>';
            $error .= '<pre>' . print_r($result, true) . '</pre>';
            throw new Exception($error);
        }
        if (!isset($decodedResult['success'])) {
            throw new Exception('Invalid Response');
        }
        if (!$decodedResult['success']) {
            $error = '<h2>No Success</h2>';
            $error .= '<p>' . $decodedResult['message'] . '</p>';
            throw new Exception($error);
        }

        return $decodedResult;
    }

    private function createCategory(string $name, int $parentId): array
    {
        return $this->post('api/categories', [
            'parentId' => $parentId,
            'name' => $name,
        ]);
    }

    private function buildArticleDataArray(array $product, array $categories): array
    {
        $this->throwExceptionWhenEmpty($product, ['name']);

        $faker = $this->faker;

        $articleData = [
            'name' => $product['name'],
            'active' => \array_key_exists('active', $product) ? $product['active'] : true,
            'description' => \array_key_exists('description',
                $product) ? $product['description'] : $faker->realText($faker->numberBetween(10, 20)),
            'description_long' => \array_key_exists('description_long',
                $product) ? $product['description_long'] : $faker->realText($faker->numberBetween(50, 200)),
            'tax' => \array_key_exists('tax', $product) ? $product['tax'] : 19,
            'supplier' => \array_key_exists('supplier', $product) ? $product['supplier'] : 'NoName GmbH',
            'priceGroupId' => \array_key_exists('priceGroupId', $product) ? $product['priceGroupId'] : 1,

            'images' => [
                ['link' => $this->assetUrl . '800x600/' . urlencode($product['name']) . '.jpg'],
            ],

            'mainDetail' => [
                'number' => \array_key_exists('number', $product) ? $product['number'] : 'swTEST' . uniqid(),
                'inStock' => \array_key_exists('inStock', $product) ? $product['inStock'] : 16,
                'active' => \array_key_exists('active', $product) ? $product['active'] : true,
                'prices' => [
                    [
                        'customerGroupKey' => \array_key_exists('customerGroupKey',
                            $product) ? $product['customerGroupKey'] : 'EK',
                        'price' => \array_key_exists('price', $product) ? $product['price'] : 99.34,
                    ],
                ],
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
     * @throws Exception
     */
    private function throwExceptionWhenEmpty(array $array, array $expectedFields): void
    {
        foreach ($expectedFields as $field) {
            if (empty($array[$field])) {
                throw new Exception("Field $field is required.");
            }
        }
    }

    private function buildCustomerDataArray(array $customer): array
    {
        $faker = $this->faker;
        $birthday = $faker->dateTimeBetween('-70 years', '-18 years')->format($faker->iso8601);

        return [
            'paymentId' => \array_key_exists('paymentId', $customer) ? $customer['paymentId'] : 5,
            'groupKey' => \array_key_exists('groupKey', $customer) ? $customer['groupKey'] : 'EK',
            'shopId' => \array_key_exists('shopId', $customer) ? $customer['shopId'] : 1,
            'priceGroupId' => \array_key_exists('priceGroupId', $customer) ? $customer['active'] : null,
            'encoderName' => \array_key_exists('encoderName', $customer) ? $customer['encoderName'] : 'bcrypt',
            'password' => $customer['password'],
            'active' => \array_key_exists('active', $customer) ? $customer['active'] : true,
            'email' => $customer['email'],
            'paymentPreset' => \array_key_exists('paymentPreset', $customer) ? $customer['paymentPreset'] : 5,
            'languageId' => \array_key_exists('languageId', $customer) ? $customer['languageId'] : '1',
            'salutation' => \array_key_exists('salutation', $customer) ? $customer['salutation'] : 'mr',
            'title' => \array_key_exists('title', $customer) ? $customer['title'] : '',
            'firstname' => \array_key_exists('firstname', $customer) ? $customer['firstname'] : $faker->firstName,
            'lastname' => \array_key_exists('lastname', $customer) ? $customer['lastname'] : $faker->lastName,
            'birthday' => \array_key_exists('birthday', $customer) ? $customer['birthday'] : $birthday,
            'country' => \array_key_exists('country', $customer) ? $customer['country'] : 2,
            'street' => \array_key_exists('street', $customer) ? $customer['street'] : $faker->streetName . ' ' . random_int(1, 101),
            'city' => \array_key_exists('city', $customer) ? $customer['city'] : $faker->city,
            'zipcode' => \array_key_exists('zip', $customer) ? $customer['zip'] : random_int(11111, 99999),
            'billing' => [
                'salutation' => \array_key_exists('salutation', $customer) ? $customer['salutation'] : 'mr',
                'title' => \array_key_exists('title', $customer) ? $customer['title'] : '',
                'firstname' => \array_key_exists('firstname', $customer) ? $customer['firstname'] : $faker->firstName,
                'lastname' => \array_key_exists('lastname', $customer) ? $customer['lastname'] : $faker->lastName,
                'birthday' => \array_key_exists('birthday', $customer) ? $customer['birthday'] : $birthday,
                'street' => \array_key_exists('street', $customer) ? $customer['street'] : $faker->streetName . ' ' . random_int(1, 101),
                'city' => \array_key_exists('city', $customer) ? $customer['city'] : $faker->city,
                'zipcode' => \array_key_exists('zip', $customer) ? $customer['zip'] : random_int(11111, 99999),
                'country' => \array_key_exists('country', $customer) ? $customer['country'] : 2,
            ],
        ];
    }

    /**
     * @throws Exception
     */
    private function getCustomerGroupIdByKey(string $key): ?int
    {
        $response = $this->get('api/customerGroups', [
            'filter' => [
                'key' => $key,
            ],
            'limit' => 1,
        ]);

        if ($response['success'] !== true) {
            throw new Exception('API communication unsuccessful: ' . print_r($response, true));
        }

        if ($response['total'] === 0) {
            return null;
        }

        return (int) $response['data'][0]['id'];
    }

    private function buildCustomerGroupDataArray(array $group): array
    {
        return [
            'key' => $group['key'],
            'name' => $group['name'],
            'tax' => \array_key_exists('tax', $group) ? $group['tax'] : true,
            'taxInput' => \array_key_exists('taxInput', $group) ? $group['taxInput'] : true,
            'mode' => \array_key_exists('mode', $group) ? $group['mode'] : false,
            'discount' => \array_key_exists('discount', $group) ? $group['discount'] : 0,
            'minimumOrder' => \array_key_exists('minimumOrder', $group) ? $group['minimumOrder'] : 10,
            'minimumOrderSurcharge' => \array_key_exists('minimumOrderSurcharge',
                $group) ? $group['minimumOrderSurcharge'] : 5,
        ];
    }

    private function buildPropertyDataArray($group): array
    {
        return [
            'key' => $group['key'],
            'name' => $group['set'],
            'description' => \array_key_exists('name', $group) ? $group['name'] : 'This is my set',
            'active' => \array_key_exists('active', $group) ? $group['active'] : true,
            'taxId' => \array_key_exists('tax', $group) ? $group['tax'] : true,
            'mainDetail' => \array_key_exists('mainDetail', $group) ? $group['mainDetail'] : [
                'number' => 'SW10002',
                'inStock' => 15,
                'active' => true,
                [
                    'customerGroupKey' => 'EK',
                    'from' => 1,
                    'price' => 50,
                ],
            ],
            'filterGroupId' => \array_key_exists('groupKey', $group) ? $group['groupKey'] : 1,
            'propertyValues' => \array_key_exists('propertyValues', $group) ? $group['propertyValues'] : [
                [
                    'option' => ['name' => $group['group']],
                    'value' => $group['option'],
                ],
            ],
        ];
    }
}
