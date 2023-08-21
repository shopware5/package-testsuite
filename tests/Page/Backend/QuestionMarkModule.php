<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Page\Backend;

use Behat\Mink\Exception\ElementNotFoundException;
use Shopware\Component\XpathBuilder\BackendXpathBuilder;

class QuestionMarkModule extends BackendModule
{
    /**
     * Gets the set version number of the installation
     *
     * @throws ElementNotFoundException
     *
     * @return string versionnr
     */
    public function getVersionNr(): string
    {
        $xpath = BackendXpathBuilder::create()
            ->child('div',
                ['~class' => 'x-about-shopware-content']
            )
            ->descendant('strong')
            ->contains('Shopware')
            ->getXpath();
        $text = $this->find('xpath', $xpath)->getHtml();

        return str_replace('Shopware ', '', $text);
    }

    /**
     * Gets the set build number of the installation
     *
     * @throws ElementNotFoundException
     *
     * @return string buildnr
     */
    public function getBuildNr()
    {
        $xpath = BackendXpathBuilder::create()
                    ->child('div',
                        ['~class' => 'x-about-shopware-content']
                    )
                    ->descendant('span')
                    ->contains('Build')
                    ->getXpath();
        $text = $this->find('xpath', $xpath)->getHtml();

        return str_replace('Build Rev ', '', $text);
    }
}
