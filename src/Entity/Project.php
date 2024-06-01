<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Source::class)]
    private Collection $sources;

    #[ORM\ManyToMany(targetEntity: Language::class, inversedBy: 'projects_target')]
    private Collection $target_languages;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'project_translator')]
    private Collection $translators;

    public function __construct()
    {
        $this->sources = new ArrayCollection();
        $this->target_languages = new ArrayCollection();
        $this->translators = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return Collection<int, Source>
     */
    public function getSources(): Collection
    {
        return $this->sources;
    }

    public function addSource(Source $source): self
    {
        if (!$this->sources->contains($source)) {
            $this->sources->add($source);
            $source->setProject($this);
        }

        return $this;
    }

    public function removeSource(Source $source): self
    {
        if ($this->sources->removeElement($source)) {
            // set the owning side to null (unless already changed)
            if ($source->getProject() === $this) {
                $source->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Language>
     */
    public function getTargetLanguages(): Collection
    {
        return $this->target_languages;
    }

    public function addTargetLanguage(Language $targetLanguage): self
    {
        if (!$this->target_languages->contains($targetLanguage)) {
            $this->target_languages->add($targetLanguage);
        }

        return $this;
    }

    public function removeTargetLanguage(Language $targetLanguage): self
    {
        $this->target_languages->removeElement($targetLanguage);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getTranslators(): Collection
    {
        return $this->translators;
    }

    public function addTranslator(User $translator): self
    {
        if (!$this->translators->contains($translator)) {
            $this->translators->add($translator);
            $translator->addProjectTranslator($this);
        }

        return $this;
    }

    public function removeTranslator(User $translator): self
    {
        if ($this->translators->removeElement($translator)) {
            $translator->removeProjectTranslator($this);
        }

        return $this;
    }
}
