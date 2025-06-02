<?php

namespace App\Entity;

use App\Repository\SeatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeatRepository::class)]
class Seat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'seats')]
    #[ORM\JoinColumn(name: 'hall_id', referencedColumnName: 'id', nullable: false)]
    private ?Hall $hall = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $searNumber = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $seatRow = null;

    #[ORM\Column(length: 50)]
    private ?string $seatType = null;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'seat')]
    private Collection $tickets;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHall(): ?Hall
    {
        return $this->hall;
    }

    public function setHall(?Hall $hall): static
    {
        $this->hall = $hall;

        return $this;
    }

    public function getSearNumber(): ?int
    {
        return $this->searNumber;
    }

    public function setSearNumber(int $searNumber): static
    {
        $this->searNumber = $searNumber;

        return $this;
    }

    public function getSeatRow(): ?int
    {
        return $this->seatRow;
    }

    public function setSeatRow(int $seatRow): static
    {
        $this->seatRow = $seatRow;

        return $this;
    }

    public function getSeatType(): ?string
    {
        return $this->seatType;
    }

    public function setSeatType(string $seatType): static
    {
        $this->seatType = $seatType;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): static
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setSeat($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getSeat() === $this) {
                $ticket->setSeat(null);
            }
        }

        return $this;
    }
}
