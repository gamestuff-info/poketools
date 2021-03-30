namespace ApiRecord {
    namespace Nature {
        interface BattleStyle extends EntityHasId,
            EntityHasNameAndSlug {
        }

        interface NatureBattleStylePreference {
            battleStyle: BattleStyle
            lowHpChance: number
            highHpChance: number
        }

        interface NaturePokeathlonStatChange {
            pokeathlonStat: PokeathlonStat
            maxChange: number
        }

        interface Nature extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug {
            statIncreased: Stat
            statDecreased: Stat
            flavorLikes: ApiRecord.Item.BerryFlavor
            flavorHates: ApiRecord.Item.BerryFlavor
            neutral: boolean
        }

        namespace Nature {
            interface NatureIndex extends Nature {
                flavorLikes: ApiRecord.Item.BerryFlavor.NatureIndex
                flavorHates: ApiRecord.Item.BerryFlavor.NatureIndex
            }

            interface NatureView extends Nature {
                flavorLikes: ApiRecord.Item.BerryFlavor.NatureView
                flavorHates: ApiRecord.Item.BerryFlavor.NatureView
                battleStylePreferences: Array<NatureBattleStylePreference>
                pokeathlonStatChanges: Array<NaturePokeathlonStatChange>
            }
        }

        interface Characteristic extends ApiRecord.Entity,
            EntityHasId,
            EntityHasFlavorText {
            stat: Stat
            ivDeterminator: number
        }
    }


    namespace Item {
        namespace BerryFlavor {
            import ContestType = ApiRecord.Type.ContestType;

            interface WithContestType {
                contestType: ContestType
            }

            interface NatureIndex extends BerryFlavor, WithContestType {
            }

            interface NatureView extends BerryFlavor, WithContestType {
            }
        }
    }
}
