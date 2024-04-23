<?php

namespace App\Interface;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

interface DomainEntityManagerInterface
{
    public function getRepository(): ServiceEntityRepository;

    public function getEm(): EntityManagerInterface;
}
