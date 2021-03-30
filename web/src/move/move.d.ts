namespace ApiRecord {
    namespace Move {
        interface DamageClass extends ApiRecord.Entity,
            EntityHasDescription,
            EntityHasNameAndSlug,
            EntityIsSortable {
        }

        interface MoveEffect extends ApiRecord.Entity,
            EntityHasDescription,
            EntityHasId {
        }

        interface MoveTarget extends ApiRecord.Entity,
            EntityHasDescription,
            EntityHasNameAndSlug {
        }

        interface ContestEffectCategory extends ApiRecord.Entity,
            EntityHasDescription,
            EntityHasNameAndSlug,
            EntityIsSortable {
        }

        interface ContestEffect extends ApiRecord.Entity,
            EntityHasDescription,
            EntityHasFlavorText {
            category: ContestEffectCategory
            appeal: number
            jam: number
        }

        interface SuperContestEffect extends ApiRecord.Entity,
            EntityHasDescription,
            EntityHasFlavorText {
            appeal: number
        }

        interface MoveFlag extends ApiRecord.Entity,
            EntityHasDescription,
            EntityHasNameAndSlug {
        }

        interface MoveStatChange extends ApiRecord.Record {
            stat: ApiRecord.Stat
            change: number
        }

        interface MoveCategory extends ApiRecord.Entity,
            EntityHasDescription,
            EntityHasNameAndSlug,
            EntityIsSortable {
        }

        interface MoveAilment extends ApiRecord.Entity,
            EntityHasDescription,
            EntityHasNameAndSlug,
            EntityIsSortable {
            volatile: boolean
        }

        interface MoveLearnMethod extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug,
            EntityHasDescription,
            EntityIsSortable {
        }

        interface MoveInVersionGroup extends ApiRecord.Entity,
            EntityGroupedByVersionGroup,
            EntityHasFlavorText,
            EntityHasId,
            EntityHasNameAndSlug {
            type: ApiRecord.Type.Type,
            power?: number
            pp?: number
            accuracy?: number
            priority: number
            effectiveDamageClass: DamageClass
            effect: MoveEffect
            effectChance?: number
            contestType?: ApiRecord.Type.ContestType
            contestEffect?: ApiRecord.ContestEffect
            superContestEffect?: ApiRecord.SuperContestEffect
        }

        namespace MoveInVersionGroup {
            interface MoveView extends MoveInVersionGroup {
                target: MoveTarget
                machine?: ApiRecord.Machine
                flags: Array<MoveFlag>
                statChanges: Array<MoveStatChange>
                categories: Array<MoveCategory>
                ailment?: MoveAilment
                ailmentChance?: number
                hits: string
                turns: string
                drain?: number
                recoil?: number
                healing?: number
                critRateBonus?: number
                flinchChance?: number
            }
        }
    }
    namespace Pokemon {
        namespace PokemonMove {
            interface MoveView extends PokemonMove {
                pokemon: Pokemon
            }
        }
    }
}
