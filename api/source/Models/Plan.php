<?php

namespace source\Models;

use Source\Core\Connect;
use Source\Core\Model;

class Plan extends Model
{
    private ?int $id;
    private ?string $name;
    private ?float $price;
    private ?int $active;

    public function __construct( ?int $id = null, ?string $name = null, ?float $price = null, ?int $active = null) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->active = $active;

        $this->table = 'plans';
        $this->primaryKey = 'id';
        $this->fillable = ['id', 'name', 'price', 'active'];
    }
    public function getId(): int
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getPrice(): float
    {
        return $this->price;
    }
    public function getActive(): string
    {
        return $this->active;
    }
    public function setId(int $id)
    {
        $this->id = $id;
    }
    public function setName(string $name)
    {
        $this->name = $name;
    }
    public function setPrice(float $price)
    {
        $this->price = $price;
    }
    public function setActive(int $active)
    {
        $this->active = $active;
    }
}