<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups('task:read')]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?float $estimatedTime = null;

    #[ORM\Column(length: 255)]
    #[Groups('task:read')]
    #[Assert\NotNull]
    private ?string $formatTime = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('task:read')]
    #[Assert\NotNull]
    private ?Priority $priority = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Project $project = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('task:read')]
    #[Assert\NotNull]
    private ?Statut $statut = null;

    /**
     * @var Collection<int, Affect>
     */
    #[ORM\OneToMany(targetEntity: Affect::class, mappedBy: 'task')]
    private Collection $members;

    #[ORM\Column(length: 255)]
    #[Groups('task:read')]
    #[Assert\NotBlank(message: "Le nom de la tâche est obligatoire.")]
    private ?string $label = null;

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getEstimatedTime(): ?float
    {
        return $this->estimatedTime;
    }

    public function setEstimatedTime(?float $estimatedTime): static
    {
        $this->estimatedTime = $estimatedTime;

        return $this;
    }

    public function getFormatTime(): ?string
    {
        return $this->formatTime;
    }

    public function setFormatTime(string $formatTime): static
    {
        $this->formatTime = $formatTime;

        return $this;
    }

    public function getPriority(): ?Priority
    {
        return $this->priority;
    }

    public function setPriority(?Priority $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getStatut(): ?Statut
    {
        return $this->statut;
    }

    public function setStatut(?Statut $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, Affect>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Affect $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->setTask($this);
        }

        return $this;
    }

    public function removeMember(Affect $member): static
    {
        if ($this->members->removeElement($member)) {
            // set the owning side to null (unless already changed)
            if ($member->getTask() === $this) {
                $member->setTask(null);
            }
        }

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }
}
