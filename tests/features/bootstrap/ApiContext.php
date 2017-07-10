<?php

namespace Shopware\Tests\Mink;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Shopware\Exception\MissingRequirementException;
use Shopware\Helper\ApiClient;

class ApiContext extends SubContext
{
    /** @var ApiClient $apiClient */
    private $apiClient;

    private $generatedUsers;

    /**
     * @AfterScenario
     */
    public function cleanGeneratedUsers(AfterScenarioScope $scope)
    {
        if ($this->generatedUsers == null || !is_array($this->generatedUsers)) {
            return;
        }

        $api = $this->getApiClient();

        foreach ($this->generatedUsers as $email) {
            if ($api->customerExists($email)) {
                $api->deleteCustomerByEmail($email);
            }
        }
    }

    /**
     * @Given the following products exist in the store:
     */
    public function theFollowingProductsExistInTheStore(TableNode $table)
    {
        $api = $this->getApiClient();
        $data = $table->getHash();

        foreach ($data as $product) {
            if ($api->articleExistsByName($product['name']) === false) {
                $api->createArticle($product);
            }
        }
    }

    /**
     * @Given the following customer Accounts exist:
     */
    public function theFollowingCustomerAccountsExist(TableNode $table)
    {
        $data = $table->getHash();
        foreach ($data as $item) {
            $this->generatedUsers[] = $item['email'];
            $password = (array_key_exists('password', $item) && !empty($item['password'])) ? $item['password'] : '';
            $group = (array_key_exists('group', $item) && !empty($item['group'])) ? $item['group'] : 'EK';
            $this->recreateCustomerAccount($item['email'], $password, $group);
        }
    }

    /**
     * @Given /^the customer account "([^"]*)"(?: with password "([^"]*)")?(?: (?:and )?with group "([^"]*)")? exists$/
     */
    public function theCustomerAccountExists($email, $password = '', $group = '')
    {
        $this->generatedUsers[] = $email;
        $this->recreateCustomerAccount($email, $password, $group);
    }

    /**
     * @Given /^the following countries are active for checkout:$/
     */
    public function theFollowingCountriesAreActiveForCheckout(TableNode $table)
    {
        $api = $this->getApiClient();
        $data = $table->getHash();

        $api->setCountryData($data);
    }

    /**
     * @Given /^the following customer groups exist:$/
     */
    public function theFollowingCustomerGroupsExist(TableNode $table)
    {
        $api = $this->getApiClient();
        $data = $table->getHash();

        foreach ($data as $customerGroup) {
            if ($api->customerGroupExistsByKey($customerGroup['key']) === false) {
                $api->createCustomerGroup($customerGroup);
            } else {
                $api->updateCustomerGroup($customerGroup);
            }
        }
    }

    /**
     * @Given there is no customer registered with e-mail address :email
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
     */
    private function recreateCustomerAccount($email, $password = '', $group = '')
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

        $api->createCustomer($data);
    }

    /**
     * @return ApiClient
     * @throws MissingRequirementException
     */
    private function getApiClient()
    {
        if ($this->apiClient !== null) {
            return $this->apiClient;
        }
        $baseUrl = $this->getMinkParameter('base_url');
        $apiUser = "demo";
        $apiKey = getenv("api_key");
        if (empty($apiKey)) {
            throw new MissingRequirementException("Please set the api_key parameter in .env");
        }

        if (empty($baseUrl)) {
            throw new MissingRequirementException("Please set the base_url parameter in behat.yml");
        }

        $assetUrl = getenv('assets_url');
        if (empty($assetUrl)) {
            throw new MissingRequirementException("Please set the asset_url parameter in .env");
        }

        $this->apiClient = new ApiClient($baseUrl, $assetUrl, $apiUser, $apiKey);

        return $this->apiClient;
    }
}
