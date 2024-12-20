<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ParticipantController extends AbstractController
{
    
    #[Route('/events/{eventId}/participants/new', name: 'participant_add')]
    public function addParticipant(
        int $eventId,
        EventRepository $eventRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $event = $eventRepository->find($eventId);

        if (!$event) {
            throw $this->createNotFoundException('Event not found.');
        }

        $participant = new Participant();
        $participant->setEvent($event);

        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('participant_success', 'Le participant a été ajouté avec succès.');

            return $this->redirectToRoute('event_view', ['id' => $eventId]);
        }

        return $this->render('participant/new.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }
}
