<?php
// src/OC/PlatformBundle/DataFixtures/ORM/LoadAdvert.php

namespace OC\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Application;

class LoadAdvert implements FixtureInterface
{
    // Dans l'argument de la méthode load, l'objet $manager est l'EntityManager
    public function load(ObjectManager $manager)
    {
        // Liste des noms de catégorie à ajouter
        $ads = array(
            array(
                new \DateTime('2017-06-16 16:24:02'), 
                'Developer Symfony', 
                'Jane Doe', 
                'Ceci est une annonce de ...'
            ),
            array(
                new \DateTime('2018-03-16 16:24:02'), 
                'Developper JS', 
                'La', 
                'Ceci est une annonce de ...'
            ),
            array(
                new \DateTime('2018-02-16 16:24:02'), 
                'Developper PHP', 
                'Lo', 
                'Ceci est une annonce de ...'
            ),
            array(
                new \DateTime('2018-06-16 16:24:02'), 
                'Graphiste', 
                'John Doe', 
                'Ceci est une annonce de ...'
            )
        );

        foreach ($ads as $key=>$ad) {
        // On crée la catégorie
            $advert = new Advert();
            $advert->setDate($ad[0]);
            $advert->setTitle($ad[1]);
            $advert->setAuthor($ad[2]);
            $advert->setContent($ad[3]);

            if($key % 2 === 1) {
                // Création d'une première candidature
                $application = new Application();
                $application->setAuthor('Marine');
                $application->setContent("J'ai toutes les qualités requises.");


                // On lie les candidatures à l'annonce
                $advert->addApplication($application);
            }

            // On la persiste
            $manager->persist($advert);
        }

        // On déclenche l'enregistrement de toutes les catégories
        $manager->flush();
    }
}