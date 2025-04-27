<?php

namespace App\Controller;


use App\Entity\Appointments;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HelloController extends AbstractController
{
    #[Route('/', name: 'main_page')]
    public function index(): Response
    {
        return $this->render('main_page.html.twig');
    }

    #[Route('/create-appointment', name: 'create_appointment', methods: ['POST'])]
    public function create_appointment(EntityManagerInterface $entitymanager): Response
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST"){
            $appointment = new Appointments();
            $patient = htmlspecialchars(trim($_POST["patient"]));
            $doctor = htmlspecialchars(trim($_POST["doctor"]));
            $date = htmlspecialchars(trim($_POST["date"]));
            $appointment_date = \DateTime::createFromFormat('Y-m-d', $date);
            $appointment->setAppointmentPatientName($patient);
            $appointment->setAppointmentDoctorName($doctor);
            $appointment->setAppointmentDate($appointment_date);
            return new Response("Запись успешно добавлена");
        }
    }
}