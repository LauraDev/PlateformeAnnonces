<?php
// src/OC/PlatformBundle/DataFixtures/ORM/LoadAdvert.php

namespace OC\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Entity\Skill;
use OC\PlatformBundle\Entity\Image;

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

        $skill = new Skill();
        $skill->setName('Indesign');
        $manager->persist($skill);

        foreach ($ads as $key=>$ad) {
        // On crée la catégorie
            $advert = new Advert();
            $advert->setDate($ad[0]);
            $advert->setTitle($ad[1]);
            $advert->setAuthor($ad[2]);
            $advert->setContent($ad[3]);
            $advert->setAuthorEmail('loles34_4@hotmail.com');

            if($key % 2 === 1) {
                // Création d'une première candidature
                $application = new Application();
                $application->setAuthor('Marine');
                $application->setContent("J'ai toutes les qualités requises.");


                // On lie les candidatures à l'annonce
                $advert->addApplication($application);

            }
            // Creation de l'entite image
            $image = new Image();
            $image-> setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
            $image-> setAlt('Job de reve!');
            
            // Relation image - annonce
            $advert-> setImage($image);

            $advertSkill = new AdvertSkill();
            $advertSkill->setLevel('Junior');
            $advertSkill->setSkill($skill);

            $advert->addAdvertSkill($advertSkill);

            // On la persiste
            $manager->persist($advert);
        }

        // On déclenche l'enregistrement de toutes les catégories
        $manager->flush();
    }
}