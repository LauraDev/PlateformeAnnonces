<?php
// src/OC/PlatformBundle/Antispam/OCPurge.php

namespace OC\PlatformBundle\Purge;

use OC\PlatformBundle\Entity\Advert;

class OCPurge
{
    private $_em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->_em     = $em;
    }

    /**
     * Effacer les annonces sans commentaires qui ont plus de X jours
     *
     * @param int $days
     * @return string
     */

    public function purge($days)
    {
        $repository = $this->_em->getRepository('OCPlatformBundle:Advert')
        ;
        
        $adsToPurge = $repository->findAdvertsToPurge($days);
        return $adsToPurge;

    }
}