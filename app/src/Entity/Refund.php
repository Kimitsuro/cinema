<?php

namespace App\Entity;

use App\Repository\RefundRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefundRepository::class)]
class Refund
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'refunds')]
    #[ORM\JoinColumn(name: 'goer_email', referencedColumnName: 'goer_email', nullable: false)]
    private ?Goer $goer = null;

    #[ORM\Column]
    private ?\DateTime $refundDate = null;

    #[ORM\Column(length: 500)]
    private ?string $refundReason = null;

    #[ORM\Column(length: 50)]
    private ?string $refundStatus = null;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'refund')]
    private Collection $tickets;

    #[ORM\ManyToOne(inversedBy: 'refunds')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ticket $ticket = null;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGoer(): ?Goer
    {
        return $this->goer;
    }

    public function setGoer(?Goer $goer): static
    {
        $this->goer = $goer;

        return $this;
    }

    public function getRefundDate(): ?\DateTime
    {
        return $this->refundDate;
    }

    public function setRefundDate(\DateTime $refundDate): static
    {
        $this->refundDate = $refundDate;

        return $this;
    }

    public function getRefundReason(): ?string
    {
        return $this->refundReason;
    }

    public function setRefundReason(string $refundReason): static
    {
        $this->refundReason = $refundReason;

        return $this;
    }

    public function getRefundStatus(): ?string
    {
        return $this->refundStatus;
    }

    public function setRefundStatus(string $refundStatus): static
    {
        $this->refundStatus = $refundStatus;

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
            $ticket->setRefund($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getRefund() === $this) {
                $ticket->setRefund(null);
            }
        }

        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): static
    {
        $this->ticket = $ticket;

        return $this;
    }
}
