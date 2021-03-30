namespace ApiRecord {
    namespace Type {
        interface Type extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug,
            EntityIsSortable {
            damageClass?: ApiRecord.Move.DamageClass
            hidden: boolean
        }

        interface ContestType extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug,
            EntityIsSortable {
        }

        interface TypeEfficacy extends ApiRecord.Entity, EntityIsSortable {
            attackingType: string
            defendingType: string
            efficacy: number
        }

        interface TypeDamage extends ApiRecord.Entity {
            type: Type
            efficacy: number
        }

        interface TypeChart extends ApiRecord.Entity, EntityHasId {
        }
    }
}

/**
 * Map Attacking type iri > Defending type iri > efficacy percentage
 */
type TypeEfficacyMap = Record<string, Record<string, number>>;
