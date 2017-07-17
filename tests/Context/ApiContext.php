<?php

namespace Shopware\Context;

use Behat\Gherkin\Node\TableNode;
use Shopware\Exception\MissingRequirementException;
use Shopware\Component\Api\ApiClient;

class ApiContext extends SubContext
{
    /**
     * @var ApiClient $apiClient
     */
    private $apiClient;

    /**
     * @Given the following products exist in the store:
     * @param TableNode $table
     */
    public function theFollowingProductsExistInTheStore(TableNode $table)
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
     * @param TableNode $table
     */
    public function theFollowingCustomerAccountsExist(TableNode $table)
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
     * @param TableNode $table
     */
    public function theFollowingCountriesAreActiveForCheckout(TableNode $table)
    {
        $api = $this->getApiClient();
        $data = $table->getHash();

        $api->setCountryData($data);
    }

    /**
     * @Given the following customer groups exist:
     * @param TableNode $table
     */
    public function theFollowingCustomerGroupsExist(TableNode $table)
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
     * @Given there is no customer registered with e-mail address :email
     * @param string $email
     */
    public function thereIsNoCustomerRegisteredWithEMailAddress($email)
    {
        $api = $this->getApiClient();
        if ($api->customerExists($email)) {
            $api->deleteCustomerByEmail($email);
        }
    }

    /**
     * @Given the category tree :tree exists
     * @param string $tree
     */
    public function theFollowingCategoryIsAvailable($tree)
    {
        $api = $this->getApiClient();
        $api->createCategoryTree($tree);
    }

    /**
     * @param string $email
     * @param string $password
     * @param string $group
     * @param string $country
     */
    private function createCustomer($email, $password = '', $group = '', $country = '')
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
     * @return ApiClient
     * @throws MissingRequirementException
     */
    private function getApiClient()
    {
        if (null !== $this->apiClient) {
            return $this->apiClient;
        }

        $baseUrl = $this->getMinkParameter('base_url');
        $apiUser = "demo";
        $apiKey = getenv("api_key");
        $assetUrl = getenv('assets_url');

        if (empty($apiKey)) {
            throw new MissingRequirementException("Please set the api_key parameter in .env");
        }

        if (empty($baseUrl)) {
            throw new MissingRequirementException("Please set the base_url parameter in behat.yml");
        }

        if (empty($assetUrl)) {
            throw new MissingRequirementException("Please set the asset_url parameter in .env");
        }

        $this->apiClient = new ApiClient($baseUrl, $assetUrl, $apiUser, $apiKey);

        return $this->apiClient;
    }
}
