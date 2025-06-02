<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'movies')]
    #[ORM\JoinColumn(name: 'admin_login', referencedColumnName: 'admin_login', nullable: false)]
    private ?Admin $admin = null;

    #[ORM\Column(length: 100)]
    private ?string $movieTitle = null;

    #[ORM\Column(length: 50)]
    private ?string $movieGenre = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $movieDuration = null;

    #[ORM\Column(length: 100)]
    private ?string $movieFormat = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $movieAge = null;

    #[ORM\Column(length: 255)]
    private ?string $moviePoster = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $movieDescription = null;

    /**
     * @var Collection<int, Session>
     */
    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'movie', orphanRemoval: true)]
    private Collection $sessions;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMovieTitle(): ?string
    {
        return $this->movieTitle;
    }

    public function setMovieTitle(string $movieTitle): static
    {
        $this->movieTitle = $movieTitle;

        return $this;
    }

    public function getMovieGenre(): ?string
    {
        return $this->movieGenre;
    }

    public function setMovieGenre(string $movieGenre): static
    {
        $this->movieGenre = $movieGenre;

        return $this;
    }

    public function getMovieDuration(): ?int
    {
        return $this->movieDuration;
    }

    public function setMovieDuration(int $movieDuration): static
    {
        $this->movieDuration = $movieDuration;

        return $this;
    }

    public function getMovieFormat(): ?string
    {
        return $this->movieFormat;
    }

    public function setMovieFormat(string $movieFormat): static
    {
        $this->movieFormat = $movieFormat;

        return $this;
    }

    public function getMovieAge(): ?int
    {
        return $this->movieAge;
    }

    public function setMovieAge(int $movieAge): static
    {
        $this->movieAge = $movieAge;

        return $this;
    }

    public function getMoviePoster(): ?string
    {
        return $this->moviePoster;
    }

    public function setMoviePoster(string $moviePoster): static
    {
        $this->moviePoster = $moviePoster;

        return $this;
    }

    public function getMovieDescription(): ?string
    {
        return $this->movieDescription;
    }

    public function setMovieDescription(string $movieDescription): static
    {
        $this->movieDescription = $movieDescription;

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
            $session->setMovie($this);
        }

        return $this;
    }

    public function removeSession(Session $session): static
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getMovie() === $this) {
                $session->setMovie(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
