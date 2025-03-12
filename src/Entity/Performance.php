<?php

namespace App\Entity;

use App\Enum\Gender;
use App\Repository\PerformanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PerformanceRepository::class)]
#[ORM\Index(columns: ['trial', 'year', 'gender', 'category', 'location', 'time'])]
class Performance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    #[Assert\DisableAutoMapping] // we do not want the validator perform a UniqueEntity check, it'll be handled by the raw sql query (ON DUPLICATE KEY UPDATE)
    private ?string $uid = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?int $time = null;

    #[ORM\Column(length: 10)]
    #[Assert\Length(max: 10)]
    #[Assert\NotBlank]
    private ?string $timeFormatted = null;

    #[ORM\Column(length: 100)]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    private ?string $location = null;

    #[ORM\Column(length: 10, enumType: Gender::class)]
    #[Assert\NotNull]
    private ?Gender $gender = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 4, nullable: true)]
    #[Assert\Length(max: 4)]
    private ?string $birth = null;

    #[ORM\Column(length: 3, nullable: true)]
    #[Assert\Length(max: 3)]
    private ?string $category = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $club = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Length(max: 10)]
    private ?string $league = null;

    #[ORM\Column(length: 3, nullable: true)]
    #[Assert\Length(max: 3)]
    private ?string $zip = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?int $trial = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?int $year = null;

    public function __toString() : string
    {
        return $this->timeFormatted ?? '';
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(?string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTime() : ?int
    {
        return $this->time;
    }

    public function setTime(?int $time) : self
    {
        $this->time = $time;

        return $this;
    }

    public function getTimeFormatted() : ?string
    {
        return $this->timeFormatted;
    }

    public function setTimeFormatted(?string $timeFormatted) : self
    {
        $this->timeFormatted = $timeFormatted;

        return $this;
    }

    public function getLocation() : ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location) : self
    {
        $this->location = $location;

        return $this;
    }

    public function getGender() : ?Gender
    {
        return $this->gender;
    }

    public function setGender(?Gender $gender) : self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function setName(?string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    public function getBirth() : ?string
    {
        return $this->birth;
    }

    public function setBirth(?string $birth = null) : self
    {
        $this->birth = $birth;

        return $this;
    }

    public function getCategory() : ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category = null) : self
    {
        $this->category = $category;

        return $this;
    }

    public function getClub() : ?string
    {
        return $this->club;
    }

    public function setClub(?string $club = null) : self
    {
        $this->club = $club;

        return $this;
    }

    public function getLeague() : ?string
    {
        return $this->league;
    }

    public function setLeague(?string $league = null) : self
    {
        $this->league = $league;

        return $this;
    }

    public function getZip() : ?string
    {
        return $this->zip;
    }

    public function setZip(?string $zip = null) : self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getTrial() : ?int
    {
        return $this->trial;
    }

    public function setTrial(?int $trial) : self
    {
        $this->trial = $trial;

        return $this;
    }

    public function getYear() : ?int
    {
        return $this->year;
    }

    public function setYear(?int $year) : self
    {
        $this->year = $year;

        return $this;
    }
}
