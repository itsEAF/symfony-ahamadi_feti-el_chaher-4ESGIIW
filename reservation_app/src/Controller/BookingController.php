<?php

namespace App\Controller;

use App\Repository\BookingRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserApplicationRepository;
use App\Entity\UserApplication;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Booking;
use App\Form\BookingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class BookingController extends AbstractController
{
    #[Route('/', name: 'app_service_list')]
    public function listServices(ServiceRepository $serviceRepo): Response
    {
        $services = $serviceRepo->findAll();
        return $this->render('service/index.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/bookings', name: 'app_booking_list')]
    public function listBookings(BookingRepository $bookingRepository): Response
    { 
        $bookings = $bookingRepository->findAll();
        return $this->render('booking/bookings.html.twig', [
            'bookings' => $bookings,
        ]);
    }

    #[Route('booking/new/{id}', name:'app_booking_new')]
    public function create(
        BookingRepository $bookingRepository, 
        ServiceRepository $serviceRepository,
        UserApplicationRepository $userApplicationRepository,
        int $id, 
        EntityManagerInterface $em, 
        Request $request
    ): Response
    {
        $service = $serviceRepository->find($id);

        $booking = new Booking();
        
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $existingBooking = $bookingRepository->findOneBy([
                'date' => $booking->getDate(),
                'service' => $service,
            ]);
    
            if ($existingBooking) {
                $this->addFlash('error', 'Ce créneau est déjà réservé.');
            } else {
                $booking->setService($service);
                
                // Récupérer ou créer l'utilisateur (UserApplication)
                $email = $form->get('userAppli')->get('email')->getData();
                $userAppli = $userApplicationRepository->findOneBy(['email' => $email]);
                
                if (!$userAppli) {
                    // Si l'utilisateur n'existe pas, le créer
                    $userAppli = new UserApplication();
                    $userAppli->setEmail($email);
                    $userAppli->setName($form->get('userAppli')->get('name')->getData());
                    $em->persist($userAppli);
                }
                
                $booking->setUserAppli($userAppli);
                
                $em->persist($booking);
                $em->flush();
    
                $this->addFlash('success', 'Réservation confirmée avec succès !');
            }
        }
    
        return $this->render('booking/create.html.twig', [
            'form' => $form->createView(),
            'service' => $service,
        ]);
    }
      
    #[Route('/booking/{id}/cancel', name: 'app_booking_cancel', requirements: ['id' => '\d+'])]
    public function cancelBooking(BookingRepository $bookingRepository, int $id, EntityManagerInterface $em): Response
    {
        $booking = $bookingRepository->find($id);

        $em->remove($booking);
        $em->flush();

        return $this->render('booking/cancel_confirmation.html.twig', [
            'booking' => $booking
        ]);
    }   
}
