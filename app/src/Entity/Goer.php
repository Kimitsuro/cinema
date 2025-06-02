<?php

namespace App\Entity;

use App\Repository\GoerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: GoerRepository::class)]
class Goer implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(length: 320)]
    private ?string $goerEmail = null;

    #[ORM\Column(length: 100)]
    private ?string $goerName = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $goerPhone = null;

    #[ORM\Column]
    private ?\DateTime $goerDate = null;

    #[ORM\Column(length: 255)]
    private ?string $goerPassword = null;

    /**
     * @var Collection<int, Purchase>
     */
    #[ORM\OneToMany(targetEntity: Purchase::class, mappedBy: 'goer')]
    private Collection $purchases;

    /**
     * @var Collection<int, Refund>
     */
    #[ORM\OneToMany(targetEntity: Refund::class, mappedBy: 'goer')]
    private Collection $refunds;

    public function __construct()
    {
        $this->purchases = new ArrayCollection();
        $this->refunds = new ArrayCollection();
    }

    public function getGoerEmail(): ?string
    {
        return $this->goerEmail;
    }

    public function setGoerEmail(string $goerEmail): static
    {
        $this->goerEmail = $goerEmail;

        return $this;
    }

    public function getGoerName(): ?string
    {
        return $this->goerName;
    }

    public function setGoerName(string $goerName): static
    {
        $this->goerName = $goerName;

        return $this;
    }

    public function getGoerPhone(): ?string
    {
        return $this->goerPhone;
    }

    public function setGoerPhone(?string $goerPhone): static
    {
        $this->goerPhone = $goerPhone;

        return $this;
    }

    public function getGoerDate(): ?\DateTime
    {
        return $this->goerDate;
    }

    public function setGoerDate(\DateTime $goerDate): static
    {
        $this->goerDate = $goerDate;

        return $this;
    }

    public function getGoerPassword(): ?string
    {
        return $this->goerPassword;
    }

    public function setGoerPassword(string $goerPassword): static
    {
        $this->goerPassword = $goerPassword;

        return $this;
    }

    /**
     * @return Collection<int, Purchase>
     */
    public function getPurchases(): Collection
    {
        return $this->purchases;
    }

    public function addPurchase(Purchase $purchase): static
    {
        if (!$this->purchases->contains($purchase)) {
            $this->purchases->add($purchase);
            $purchase->setGoer($this);
        }

        return $this;
    }

    public function removePurchase(Purchase $purchase): static
    {
        if ($this->purchases->removeElement($purchase)) {
            // set the owning side to null (unless already changed)
            if ($purchase->getGoer() === $this) {
                $purchase->setGoer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Refund>
     */
    public function getRefunds(): Collection
    {
        return $this->refunds;
    }

    public function addRefund(Refund $refund): static
    {
        if (!$this->refunds->contains($refund)) {
            $this->refunds->add($refund);
            $refund->setGoer($this);
        }

        return $this;
    }

    public function removeRefund(Refund $refund): static
    {
        if ($this->refunds->removeElement($refund)) {
            // set the owning side to null (unless already changed)
            if ($refund->getGoer() === $this) {
                $refund->setGoer(null);
            }
        }

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_GOER'];
    }

    public function getPassword(): ?string
    {
        return $this->goerPassword;
    }

    public function getUserIdentifier(): string
    {
        return $this->goerEmail;
    }

    public function eraseCredentials(): void
    {
        // Если нужно удалить временные данные
    }
}
