<?php

namespace Suffle\Snapshot\Traits;

/**
 * This file is part of the Suffle.Snapshot package
 *
 * (c) 2018
 * sebastian Flor <sebastian@flor.rocks>
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Repository\SiteRepository;

/**
 * Utility trait to determine package keys
 */
trait PackageTrait
{

    /**
     * @return array
     */
    protected function getFirstOnlineSitePackage()
    {
        $siteRepository = new SiteRepository();
        $sitePackage = $siteRepository->findFirstOnline();

        return array(
            'packageKey' => $sitePackage->getSiteResourcesPackageKey(),
            'baseUri' => $this->generateBaseUri($sitePackage->getPrimaryDomain())
        );
    }

    /**
     * @return string
     */
    protected function getFirstOnlineSitePackageKey()
    {
        $sitePackage = $this->getFirstOnlineSitePackage();

        return $sitePackage['packageKey'];
    }

    /**
     * @param String $packageKey
     * @return array
     */
    protected function getSitePackageByKey(String $packageKey): array
    {
        $siteRepository = new SiteRepository();
        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = $siteRepository->findOneBySiteResourcesPackageKey($packageKey);

        if ($site) {
            return [
                'packageKey' => $site->getSiteResourcesPackageKey(),
                'baseUri' => $this->generateBaseUri($site->getPrimaryDomain())
            ];
        }

        return [];
    }

    /**
     * @return array
     */
    protected function getSitePackages(): array
    {
        $siteRepository = new SiteRepository();
        $sitePackages = $siteRepository->findAll()->toArray();

        return array_reduce(
            $sitePackages,
            function (array $packages, Site $site) {
                $packages[$site->getSiteResourcesPackageKey()] = [
                    'packageKey' => $site->getSiteResourcesPackageKey(),
                    'baseUri' => $this->generateBaseUri($site->getPrimaryDomain()),
                ];
                return $packages;
            },
            []
        );
    }


    /**
     * @param $domain \Neos\Neos\Domain\Model\Domain
     * @return string
     */
    private function generateBaseUri($domain)
    {
        if (!$domain) {
            return '';
        }

        $scheme = $domain->getScheme();
        $port = $domain->getPort();

        $baseUri = '';
        $baseUri .= $scheme ?: 'http';
        $baseUri .= '://';
        $baseUri .= $domain->getHostname();

        if ($port !== null) {
            switch ($scheme) {
                case 'http':
                    $baseUri .= ($port !== 80 ? ':' . $port : '');
                    break;
                case 'https':
                    $baseUri .= ($port !== 443 ? ':' . $port : '');
                    break;
                default:
                    $baseUri .= (isset($port) ? ':' . $port : '');
            }
        }
        return $baseUri;
    }
}
