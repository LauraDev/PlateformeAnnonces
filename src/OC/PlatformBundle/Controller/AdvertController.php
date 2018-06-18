<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdvertController extends Controller
{
    public function indexAction($page)
    {
        if ($page < 1) {
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }
        
        $nbPerPage = 3;

        // On récupère notre objet Paginator
        $listAdverts = $this->getDoctrine()
            ->getManager()
            ->getRepository('OCPlatformBundle:Advert')
            ->getAdverts($page, $nbPerPage)
        ;

        // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
        $nbPages = ceil(count($listAdverts) / $nbPerPage);

        // Si la page n'existe pas, on retourne une 404
        if ($page > $nbPages) {
        throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
            'listAdverts' => $listAdverts,
            'nbPages'     => $nbPages,
            'page'        => $page,
        ));
        
    }

    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }
    
        return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
            'advert' => $advert
        ));
    }

    /**
    * @Security("has_role('ROLE_AUTEUR')")
    */
    public function addAction(Request $request)
    {
    // On crée un objet Advert
    $advert = new Advert();

    $form = $this->createForm(AdvertType::class, $advert);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

        $em = $this->getDoctrine()->getManager();
        $em->persist($advert);
        $em->flush();

        $request->getSession()->getFlashBag()->add('info', 'Annonce bien enregistrée.');

        return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));

    }

    return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
        'form' => $form->createView()
    ));

    }

    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        $form = $this->createForm(AdvertEditType::class, $advert);

        if ($request->isMethod('POST')) 
        {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($advert);
                $em->flush();

                $request->getSession()->getFlashBag()->add('info', 'Annonce bien modifée.');

                return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
            }
        }
    
        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
        'advert' => $advert,
        'form' => $form->createView()
        ));

    }

    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
    
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
    
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }
    
        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->get('form.factory')->create();
    
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->remove($advert);
            $em->flush();
    
            $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");
    
            return $this->redirectToRoute('oc_platform_home');
        }
        
        return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
            'advert' => $advert,
            'form'   => $form->createView(),
        ));
    }




    public function menuAction($limit)
    {
        $em = $this->getDoctrine()->getManager();

        $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->findBy(
            array(),                 // Pas de critère
            array('date' => 'desc'), // On trie par date décroissante
            $limit,                  // On sélectionne $limit annonces
            0                        // À partir du premier
        );

        return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
            'listAdverts' => $listAdverts
        ));
    }
    

    public function allCategoriesAction()
    {
        $repository = $this->getDoctrine()
        ->getManager()
        ->getRepository('OCPlatformBundle:Category')
        ;

        $categories = $repository->findAll();

        return $this->render('OCPlatformBundle:Advert:categories.html.twig', array(
        'categories' => $categories
        ));
    }

    public function editImageAction($advertId)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($advertId);

        // On modifie l'URL de l'image par exemple
        $advert->getImage()->setUrl('test.png');

        // On n'a pas besoin de persister l'annonce ni l'image.
        // Rappelez-vous, ces entités sont automatiquement persistées car
        // on les a récupérées depuis Doctrine lui-même
        
        // On déclenche la modification
        $em->flush();

        return new Response('OK');
    }

    public function purgeAction(Request $request, $days)
    {
        // On récupère le service
        $purge = $this->container
            ->get('oc_platform.purger.advert')
            ->purge($days);
        
        $request->getSession()->getFlashBag()->add('info', 'Annonces bien purgées.');
        
        return $this->redirectToRoute('oc_platform_home');
    }

    public function categoryAction($category)
    {
        $em = $this->getDoctrine()->getManager();
        $adverts = $em->getRepository('OCPlatformBundle:Advert')->getAdvertWithCategories(array($category));
        
        return $this->render('OCPlatformBundle:Advert:adsByCategory.html.twig', array(
            'adverts' => $adverts
        ));
    }
}