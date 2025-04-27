<?php

namespace App\Entity;

use App\Repository\AppointmentsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppointmentsRepository::class)]
class Appointments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $appointment_patient_name = null;

    #[ORM\Column(length: 255)]
    private ?string $appointment_doctor_name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $appointment_date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppointmentPatientName(): ?string
    {
        return $this->appointment_patient_name;
    }

    public function setAppointmentPatientName(string $appointment_patient_name): static
    {
        $this->appointment_patient_name = $appointment_patient_name;

        return $this;
    }

    public function getAppointmentDoctorName(): ?string
    {
        return $this->appointment_doctor_name;
    }

    public function setAppointmentDoctorName(string $appointment_doctor_name): static
    {
        $this->appointment_doctor_name = $appointment_doctor_name;

        return $this;
    }

    public function getAppointmentDate(): ?\DateTimeInterface
    {
        return $this->appointment_date;
    }

    public function setAppointmentDate(\DateTimeInterface $appointment_date): static
    {
        $this->appointment_date = $appointment_date;

        return $this;
    }
}