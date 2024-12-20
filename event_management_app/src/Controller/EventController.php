<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Service\DistanceCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class EventController extends AbstractController
{   
    private DistanceCalculator $distanceCalculator;

    public function __construct(DistanceCalculator $distanceCalculator)
    {
        $this->distanceCalculator = $distanceCalculator;
    }

    #[Route('/events', name: 'event_list')]
    public function listEvents(EventRepository $eventRepository): Response
    {   
        $events = $eventRepository->findAll();
        return $this->render('event/list.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/events/{id}', name:'event_view')]
    public function viewEvent(int $id, EventRepository $eventRepository): Response
    {   
        $event = $eventRepository->find( $id );
        return $this->render('event/view.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/events/{id}/distance', name: 'event_distance')]
    public function calculateDistanceToEvent(
        int $id,
        Request $request,
        EventRepository $eventRepository
    ): Response {
        
        $lat = $request->query->get('lat');
        $lon = $request->query->get('lon');

        $event = $eventRepository->find($id);

        if (!is_numeric($lat) || !is_numeric($lon)) {
            $this->addFlash('error', 'Les coordonnées doivent être des valeurs numériques.');
            return $this->redirectToRoute('event_view', ['id' => $id]);
        }

        $eventLat = $event->getLatitude();
        $eventLon = $event->getLongitude();

        $distance = $this->distanceCalculator->calculateDistance($lat, $lon, $eventLat, $eventLon);
        $this->addFlash('success', 'La distance entre votre position et l\'événement "' . $event->getName() . '" est de ' . round($distance) . ' km.');

        return $this->redirectToRoute('event_view', ['id' => $id]);
    }
}
