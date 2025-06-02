<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(length: 50)]
    private ?string $employeeLogin = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'admin_login', referencedColumnName: 'admin_login', nullable: false)]
    private ?Admin $admin = null;

    #[ORM\Column(length: 255)]
    private ?string $employeePassword = null;

    #[ORM\Column(length: 100)]
    private ?string $employeeName = null;

    #[ORM\Column(length: 200)]
    private ?string $employeePosition = null;

    /**
     * @var Collection<int, Session>
     */
    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'employee')]
    private Collection $sessions;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
    }

    public function getEmployeeLogin(): ?string
    {
        return $this->employeeLogin;
    }

    public function setEmployeeLogin(string $employeeLogin): static
    {
        $this->employeeLogin = $employeeLogin;

        return $this;
    }

    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    public function setAdmin(?Admin $admin): static
    {
        $this->admin = $admin;

        return $this;
    }

    public function getEmployeePassword(): ?string
    {
        return $this->employeePassword;
    }

    public function setEmployeePassword(string $employeePassword): static
    {
        $this->employeePassword = $employeePassword;

        return $this;
    }

    public function getEmployeeName(): ?string
    {
        return $this->employeeName;
    }

    public function setEmployeeName(string $employeeName): static
    {
        $this->employeeName = $employeeName;

        return $this;
    }

    public function getEmployeePosition(): ?string
    {
        return $this->employeePosition;
    }

    public function setEmployeePosition(string $employeePosition): static
    {
        $this->employeePosition = $employeePosition;

        return $this;
    }

    /**
     * @return Collection<int, Session>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setEmployee($this);
        }

        return $this;
    }

    public function removeSession(Session $session): static
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getEmployee() === $this) {
                $session->setEmployee(null);
            }
        }

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_EMPLOYEE'];
    }

    public function getPassword(): string
    {
        return $this->employeePassword;
    }

    public function getUserIdentifier(): string
    {
        return $this->employeeLogin;
    }

    public function eraseCredentials(): void
    {
        // Если нужно удалить временные данные
    }
}
