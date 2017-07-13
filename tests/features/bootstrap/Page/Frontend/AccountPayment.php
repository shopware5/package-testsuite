<?php

namespace Shopware\Tests\Mink\Page\Frontend;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Component\XpathBuilder\FrontendXpathBuilder;

class AccountPayment extends Page
{
    public function paymentMethodIsActive($paymentMethod)
    {
        return count($this->find('xpath', FrontendXpathBuilder::create()->child('div')->contains($paymentMethod)->getXpath())) > 0;
    }
}