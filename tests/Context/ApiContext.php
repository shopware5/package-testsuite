<?php

declare(strict_types=1);

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Exception;
use RuntimeException;
use Shopware\Component\Api\ApiClient;

class ApiContext extends SubContext
{
    private ?ApiClient $apiClient = null;

    /**
     * @Given the following products exist in the store:
     */
    public function theFollowingProductsExistInTheStore(TableNode $table): void
    {
        $api = $this->getApiClient();

        foreach ($table->getHash() as $product) {
            if (!$api->articleExistsByName($product['name'])) {
                $api->createArticle($product);
            }
        }
    }

    /**
     * @Given the following customer accounts exist:
     */
    public function theFollowingCustomerAccountsExist(TableNode $table): void
    {
        foreach ($table->getHash() as $item) {
            $password = !empty($item['password']) ? $item['password'] : '';
            $group = !empty($item['group']) ? $item['group'] : 'EK';
            $country = !empty($item['country']) ? $item['country'] : '';
            $this->createCustomer($item['email'], $password, $group, $country);
        }
    }

    /**
     * @Given the following countries are active for checkout:
     */
    public function theFollowingCountriesAreActiveForCheckout(TableNode $table): void
    {
        $api = $this->getApiClient();
        $data = $table->getHash();

        $api->setCountryData($data);
    }

    /**
     * @Given the following customer groups exist:
     */
    public function theFollowingCustomerGroupsExist(TableNode $table): void
    {
        $api = $this->getApiClient();

        foreach ($table->getHash() as $customerGroup) {
            if (!$api->customerGroupExistsByKey($customerGroup['key'])) {
                $api->createCustomerGroup($customerGroup);
            } else {
                $api->updateCustomerGroup($customerGroup);
            }
        }
    }

    /**
     * @Given the following properties exist in the store:
     *
     * @throws Exception
     */
    public function theFollowingPropertiesExistInTheStore(TableNode $table): void
    {
        $api = $this->getApiClient();

        foreach ($table->getHash() as $data) {
            $api->createProperty($data);
        }
    }

    /**
     * @Given there is no customer registered with e-mail address :email
     *
     * @throws RuntimeException
     */
    public function thereIsNoCustomerRegisteredWithEMailAddress(string $email): void
    {
        $api = $this->getApiClient();
        if ($api->customerExists($email)) {
            $api->deleteCustomerByEmail($email);
        }
    }

    /**
     * @Given the category tree :tree exists
     */
    public function theFollowingCategoryIsAvailable(string $tree): void
    {
        $api = $this->getApiClient();
        $api->createCategoryTree($tree);
    }

    /**
     * @Given the following orders exist:
     */
    public function theFollowingOrdersExist(TableNode $orders): void
    {
        foreach ($orders as $order) {
            $this->getApiClient()->createOrder($order);
        }
    }

    private function createCustomer(string $email, string $password = '', string $group = '', string $country = ''): void
    {
        $api = $this->getApiClient();

        if ($api->customerExists($email) === true) {
            $api->deleteCustomerByEmail($email);
        }

        $data = [
            'email' => $email,
            'password' => $password ?: $this->slugify($email),
        ];

        if (!empty($group)) {
            $data['groupKey'] = $group;
        }

        if (!empty($country)) {
            $data['country'] = $this->getApiClient()->getCountryIdByISO($country);
        }

        $api->createCustomer($data);
    }

    /**
     * @throws RuntimeException
     */
    private function getApiClient(): ApiClient
    {
        if ($this->apiClient !== null) {
            return $this->apiClient;
        }

        $baseUrl = $this->getMinkParameter('base_url');
        $apiUser = 'demo';
        $apiKey = getenv('api_key');
        $assetUrl = getenv('assets_url');

        if (empty($apiKey)) {
            throw new RuntimeException('Please set the api_key parameter in .env');
        }

        if (empty($baseUrl)) {
            throw new RuntimeException('Please set the base_url parameter in behat.yml');
        }

        if (empty($assetUrl)) {
            throw new RuntimeException('Please set the asset_url parameter in .env');
        }

        $this->apiClient = new ApiClient($baseUrl, $assetUrl, $apiUser, $apiKey);

        return $this->apiClient;
    }
}
