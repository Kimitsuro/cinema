<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
class Admin implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(length: 50)]
    private ?string $adminLogin = null;

    #[ORM\Column(length: 255)]
    private ?string $adminPassword = null;

    #[ORM\Column(length: 100)]
    private ?string $adminName = null;

    /**
     * @var Collection<int, Movie>
     */
    #[ORM\OneToMany(targetEntity: Movie::class, mappedBy: 'admin')]
    private Collection $movies;

    public function __construct()
    {
        $this->movies = new ArrayCollection();
    }

    public function getAdminLogin(): ?string
    {
        return $this->adminLogin;
    }

    public function setAdminLogin(string $adminLogin): static
    {
        $this->adminLogin = $adminLogin;

        return $this;
    }

    public function getAdminPassword(): ?string
    {
        return $this->adminPassword;
    }

    public function setAdminPassword(string $adminPassword): static
    {
        $this->adminPassword = $adminPassword;

        return $this;
    }

    public function getAdminName(): ?string
    {
        return $this->adminName;
    }

    public function setAdminName(string $adminName): static
    {
        $this->adminName = $adminName;

        return $this;
    }

    /**
     * @return Collection<int, Movie>
     */
    public function getMovies(): Collection
    {
        return $this->movies;
    }

    public function addMovie(Movie $movie): static
    {
        if (!$this->movies->contains($movie)) {
            $this->movies->add($movie);
            $movie->setAdmin($this);
        }

        return $this;
    }

    public function removeMovie(Movie $movie): static
    {
        if ($this->movies->removeElement($movie)) {
            // set the owning side to null (unless already changed)
            if ($movie->getAdmin() === $this) {
                $movie->setAdmin(null);
            }
        }

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_ADMIN'];
    }

    public function getPassword(): ?string
    {
        return $this->adminPassword;
    }

    
    public function getUserIdentifier(): string
    {
        return $this->adminLogin;
    }

    public function eraseCredentials(): void
    {
        // Если нужно удалить временные данные
    }
}
