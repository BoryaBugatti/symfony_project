<?php


namespace App\Controller;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Users;
use App\Entity\Appointments;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;


final class LkController extends AbstractController
{
    #[Route('/lk', name: 'app_lk')]
    public function index(SessionInterface $session): Response
    {
        if ($session->has('user_email')){
            $user_email = $session->get('user_email');
            $user_name = $session->get('user_name');
            $user_role = $session->get('user_role');
            return $this->render('lk/user_lk.html.twig',[
                'user_email'=> $user_email,
                'user_name'=> $user_name,
                'user_role'=> $user_role,
            ]);
        }
        return $this->render('lk/index.html.twig', [
            'controller_name' => 'LkController',
        ]);
    }
    #[Route('/reg', name: 'create_reg_page')]
    public function create_reg_page(): Response
    {
        return $this->render('lk/reg.html.twig');
    }
    #[Route('/athoriz', name: 'create_athoriz_page')]
    public function create_athoriz_page():Response
    {
        return $this->render('lk/athoriz.html.twig');
    }
    #[Route('/create_new_user', name: 'create_user', methods: ['POST'])]
    public function create_new_user(EntityManagerInterface $entitymanager): Response
    {
        if ($_SERVER['REQUEST_METHOD'] === "POST"){
            $user_email = htmlspecialchars(trim($_POST['user_email']));
            $user_name = htmlspecialchars(trim($_POST['user_name']));
            $user_password = htmlspecialchars(trim($_POST['user_password']));
            if (empty($user_email) || empty($user_name) || empty($user_password)){
                return new Response('<script>
                alert("Вы отправили пустые данные");
                window.location.href="/reg";
                </script>');
            }
            $user = new Users();
            $user->setUserEmail($user_email);
            $user->setUserName($user_name);
            $user->setUserPassword(password_hash($user_password, PASSWORD_DEFAULT));
            $user->setUserRole('Гость');
            $entitymanager->persist($user);
            $entitymanager->flush();
            return new Response('<script>
            alert("Вы успешно зарегистрировались");
            window.location.href="/";
            </script>');
        }
    }
    #[Route('/lk/user_lk', name: 'athorization', methods: ['POST'])]
    public function athoriz(EntityManagerInterface $entitymanager, SessionInterface $session): Response
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST"){
            $user_email = htmlspecialchars(trim($_POST['user_email']));
            $user_password = htmlspecialchars(trim($_POST['user_password']));
            $user = $entitymanager->getRepository(Users::class)->findOneBy(['user_email' => $user_email]);
            if ($user && password_verify($user_password, $user->getUserPassword())){
                $session->set('user_email', $user_email);
                $session->set('user_name', $user->getUserName());
                $session->set('user_role', $user->getUserRole());
                $user_name = $session->get('user_name');
                $user_role = $session->get('user_role');
                return $this->render('lk/user_lk.html.twig', [
                    'user_email' => $user_email,
                    'user_name'=> $user_name,
                    'user_role'=> $user_role,
                ]);
            }
        }
    }
    #[Route('/logout', name: 'log_out')]
    public function log_out(SessionInterface $session):Response
    {
        $session->clear();
        return new Response("<script>window.location.href='/'</script>");
    }

    #[Route('/lk/all_appointments', name: 'AllAppointments')]
    public function get_all_appointments(EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $all_appointments = $entityManager->getRepository(Appointments::class)->findAll();

        $appointmentsData = [];
        foreach ($all_appointments as $appointment) {
            $appointmentsData[] = [
                'appointment_patient_name' => $appointment->getAppointmentPatientName(),
                'appointment_doctor_name' => $appointment->getAppointmentDoctorName(), 
                'appointment_date' => $appointment->getAppointmentDate()->format('Y-m-d'), 
            ];
        }
        if ($session->has('user_email')){
            return $this->render('lk/all_appointments.html.twig', [
                'appointments' => $appointmentsData,
            ]);
        }
        return new Response('<script>window.location.href="/"</script>');
    }
    #[Route('/lk/all_users', name: 'AllUsers')]
    public function get_all_users(EntityManagerInterface $entitymanager, SessionInterface $session): Response
    {
        $all_users = $entitymanager->getRepository(Users::class)->findAll();
        $usersData = [];
        foreach ($all_users as $user) {
            $usersData[] = [
                'user_name' => $user->getUserName(),
                'user_email' => $user->getUserEmail(), 
                'user_role' => $user->getUserRole(),
            ];
        }
        if ($session->has('user_email')){
            return $this->render('lk/all_users.html.twig', [
                'users' => $usersData,
            ]);
        }
        return new Response('<script>window.location.href="/"</script>');
    }
    #[Route('/lk/all_users/downloadPDFreport_users', name: 'UsersPDFReport')]
    public function downloadPDFReport_users(EntityManagerInterface $entitymanager) {
        $dompdf = new Dompdf();
        $html = '<h1>Users Report</h1>';
        $all_users = $entitymanager->getRepository(Users::class)->findAll();
        $usersData = [];
        foreach ($all_users as $user) {
            $usersData[] = [
                'user_name' => $user->getUserName(),
                'user_email' => $user->getUserEmail(), 
                'user_role' => $user->getUserRole(),
            ];
        }
        foreach ($usersData as $user) {
            $html .= '<p>' . htmlspecialchars($user['user_name']) . ' - ' . htmlspecialchars($user['user_email']) . '</p>';
        }
    
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('users_report.pdf'); 
    }
    #[Route('/lk/all_appointments/downloadPDFreport_appointments', name: 'AppointmentsPDFReport')]
    public function downloadPDFReport_appointments(EntityManagerInterface $entitymanager) {
        $dompdf = new Dompdf();
        $html = '<h1>Appointments Report</h1>';
        $all_appointments = $entitymanager->getRepository(Appointments::class)->findAll();

        $appointmentsData = [];
        foreach ($all_appointments as $appointment) {
            $appointmentsData[] = [
                'appointment_patient_name' => $appointment->getAppointmentPatientName(),
                'appointment_doctor_name' => $appointment->getAppointmentDoctorName(), 
                'appointment_date' => $appointment->getAppointmentDate()->format('Y-m-d'), 
            ];
        }
        foreach ($appointmentsData as $appointment) {
            $html .= '<p>' . htmlspecialchars($appointment['appointment_patient_name']) . ' - ' . htmlspecialchars($appointment['appointment_doctor_name']) . htmlspecialchars($appointment['appointment_date']).'</p>';
        }
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('appointments_report.pdf'); 
    }
    #[Route('/downloadCSVreport_users', name: 'UsersCSVReport')]
    public function downloadCSVReport_users(EntityManagerInterface $entitymanager) {
        $all_users = $entitymanager->getRepository(Users::class)->findAll();
        $usersData = [];
        foreach ($all_users as $user) {
            $usersData[] = [
                'user_name' => $user->getUserName(),
                'user_email' => $user->getUserEmail(), 
                'user_role' => $user->getUserRole(),
            ];
        }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="user_report.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['User Name', 'User Email', 'User Role']); 

        foreach ($usersData as $user) {
            fputcsv($output, [$user['user_name'], $user['user_email'], $user['user_role']]);
        }

        fclose($output); 
    }

    #[Route('/downloadCSVreport_appointments', name: 'AppointmentsCSVReport')]
    public function downloadCSVReport_appointments(EntityManagerInterface $entitymanager) {
        $all_appointments = $entitymanager->getRepository(Appointments::class)->findAll();

        $appointmentsData = [];
        foreach ($all_appointments as $appointment) {
            $appointmentsData[] = [
                'appointment_patient_name' => $appointment->getAppointmentPatientName(),
                'appointment_doctor_name' => $appointment->getAppointmentDoctorName(), 
                'appointment_date' => $appointment->getAppointmentDate()->format('Y-m-d'), 
            ];
        }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="appointments_report.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Имя Пациента', 'Имя Врача', 'Дата приема']); 

        foreach ($appointmentsData as $appointment) {
            fputcsv($output, [$appointment['appointment_patient_name'], $appointment['appointment_doctor_name'], $appointment['appointment_date']]);
        }
        fclose($output); 
    }
}