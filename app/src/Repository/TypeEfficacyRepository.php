<?php

namespace App\Repository;

use App\Entity\Type;
use App\Entity\TypeChart;
use App\Entity\TypeEfficacy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TypeEfficacy|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeEfficacy|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeEfficacy[]    findAll()
 * @method TypeEfficacy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeEfficacyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeEfficacy::class);
    }

    /**
     * @param Type $attackingType
     * @param Type $defendingType
     * @param TypeChart $typeChart
     *
     * @return int|null
     */
    public function findForMatchup(Type $attackingType, Type $defendingType, TypeChart $typeChart): ?int
    {
        $qb = $this->createQueryBuilder('type_efficacy');
        $qb->select('type_efficacy.efficacy')
            ->andWhere('type_efficacy.typeChart = :typeChart')
            ->andWhere('type_efficacy.attackingType = :attackingType')
            ->andWhere('type_efficacy.defendingType = :defendingType')
            ->setParameter('typeChart', $typeChart)
            ->setParameter('attackingType', $attackingType)
            ->setParameter('defendingType', $defendingType);

        $q = $qb->getQuery();
        $q->execute();

        return $q->getSingleScalarResult();
    }
}
