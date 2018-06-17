<?php
// src/OC/PlatformBundle/Purger/AdvertPurger.php

namespace OC\PlatformBundle\Purger;

use OC\PlatformBundle\Entity\Advert;

class AdvertPurger
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

        foreach ($adsToPurge as $toPurge) {
            $this->_em->remove($toPurge);
        }
        $this->_em->flush();
        return $adsToPurge;

    }
}