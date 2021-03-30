type EvolutionConditionMap = Record<string, Array<string>>
namespace ApiRecord {
    interface Stat extends ApiRecord.Entity,
        EntityHasId,
        EntityHasNameAndSlug,
        EntityIsSortable {
    }

    interface PokeathlonStat extends ApiRecord.Entity,
        EntityHasId,
        EntityHasNameAndSlug,
        EntityIsSortable {
    }

    namespace Pokemon {
        interface PokemonWildHeldItem extends ApiRecord.Entity {
            version: string
            rate: number
        }

        namespace PokemonWildHeldItem {
            interface PokemonView extends PokemonWildHeldItem {
                item: Item.ItemInVersionGroup
            }
        }

        interface Pokedex extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug,
            EntityHasDescription,
            EntityHasDefault {
        }

        interface PokemonSpeciesPokedexNumber extends EntityHasDefault {
            pokedex: Pokedex
            number: number
        }

        interface PokemonSpeciesInVersionGroup extends ApiRecord.Entity,
            EntityHasId,
            EntityGroupedByVersionGroup,
            EntityHasNameAndSlug,
            EntityIsSortable {
            numbers: Array<PokemonSpeciesPokedexNumber>
            nationalDexNumber: number
            defaultPokemon: Pokemon
        }

        interface PokemonAbility extends EntityIsSortable {
            ability: Ability.AbilityInVersionGroup
            hidden: boolean
        }

        interface PokemonStat {
            baseValue: number
            effortChange: number
        }

        interface PokemonType extends EntityIsSortable {
            type: Type.Type
        }

        interface Pokemon extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug,
            EntityIsSortable,
            EntityHasDefault {
            speciesSlug: string
            abilities: Array<PokemonAbility>
            hp?: PokemonStat
            attack: PokemonStat
            defense: PokemonStat
            specialAttack?: PokemonStat
            specialDefense?: PokemonStat
            special?: PokemonStat
            speed: PokemonStat
            statTotal: number
            types: Array<PokemonType>
            mega: boolean
            defaultForm: PokemonForm
        }

        interface PokemonColor extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug,
            EntityIsSortable {
            cssColor: string
        }

        interface PokemonShapeInVersionGroup extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug,
            EntityHasDescription,
            EntityHasIcon,
            EntityGroupedByVersionGroup {
            taxonomyName: string
        }

        interface PokemonHabitat extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug,
            EntityHasIcon {
        }

        interface GrowthRate extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug {
            /** MathML representation of the growth rate formula */
            formula: string
        }

        interface PokemonFlavorText extends EntityIsSortable,
            EntityHasFlavorText {
            version: string
        }

        interface EggGroup extends ApiRecord.Entity, EntityHasId, EntityHasNameAndSlug {
        }

        interface PalParkArea extends ApiRecord.Entity, EntityHasId, EntityHasNameAndSlug {
        }

        interface PokemonPalParkData {
            area: PalParkArea
            score: number
            rate: number
        }

        namespace Pokemon {
            interface PokemonView extends Pokemon {
                [p: string]

                color?: PokemonColor
                shape?: PokemonShapeInVersionGroup
                habitat?: PokemonHabitat
                /** Null if genderless */
                femaleRate: number | null
                captureRate: number
                happiness?: number
                baby: boolean
                hatchSteps?: number
                growthRate: GrowthRate
                formsSwitchable: boolean
                genus: string
                formsNote: string
                flavorText: Array<PokemonFlavorText>
                eggGroups: Array<EggGroup>
                palParkData?: PokemonPalParkData
                heightCentimeters: number
                weightGrams: number
                experience: number
                wildHeldItems: Array<PokemonWildHeldItem.PokemonView>
            }

            interface EvolutionTree extends Pokemon {
                evolutionConditions: Array<PokemonEvolutionCondition>
            }
        }

        interface PokemonForm extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug,
            EntityHasDefault,
            EntityIsSortable,
            EntityHasIcon {
            speciesSlug: string
            pokemonSlug: string
            formName: string
            battleOnly: boolean
        }

        interface PokemonFormPokeathlonStat extends EntityIsSortable {
            pokeathlonStat: PokeathlonStat
            range: string
            min: number
            max: number
            baseValue: number
        }

        interface PokemonSprite extends MediaEntity {
        }

        interface PokemonArt extends MediaEntity {
        }

        namespace PokemonForm {
            interface PokemonView extends PokemonForm {
                pokeathlonStats: Array<PokemonFormPokeathlonStat>
                cry: string
                footprint: string
                sprites: Array<PokemonSprite>
                art: Array<PokemonArt>
            }
        }

        interface PokemonEvolutionTree extends ApiRecord.Entity {
            pokemon: Pokemon.EvolutionTree
            /** Trigger > list of conditions */
            conditions: EvolutionConditionMap
            children: Array<PokemonEvolutionTree>
        }

        interface PokemonMove extends ApiRecord.Entity,
            EntityHasId,
            EntityIsSortable {
            learnMethod: Move.MoveLearnMethod
            level?: number
            machine?: Item.ItemInVersionGroup
        }

        namespace PokemonMove {
            interface PokemonView extends PokemonMove {
                move: Move.MoveInVersionGroup
            }
        }

        /** Returned from the stat-specific endpoint, not bundled with Pokemon */
        interface PokemonStatInfo extends ApiRecord.Entity {
            pokemon: string
            stat: string
            baseValue: number
            percentile: number
        }

        interface LevelExperience extends ApiRecord.Entity {
            growthRate: string
            level: number
            experience: number
        }

        interface Gender extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug,
            EntityIsSortable {
        }

        interface EncounterMethod extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug,
            EntityIsSortable {
        }

        interface EncounterCondition extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug {
        }

        interface EncounterConditionState extends ApiRecord.Entity,
            EntityHasId,
            EntityHasNameAndSlug,
            EntityIsSortable,
            EntityHasDefault {
            condition: EncounterCondition
        }

        interface Encounter extends ApiRecord.Entity,
            EntityHasId,
            EntityGroupedByVersion,
            EntityIsSortable {
            method: EncounterMethod
            level?: string
            chance?: number
            conditions: Array<EncounterConditionState>
            note?: string
        }

        namespace Encounter {
            interface PokemonView extends Encounter {
                locationArea: Location.LocationArea.PokemonView
            }

            interface LocationView extends Encounter {
                pokemon: Pokemon
            }
        }
    }

    namespace Location {
        namespace LocationInVersionGroup {
            interface PokemonView extends LocationInVersionGroup, WithLocationMap {
            }
        }

        namespace LocationArea {
            interface PokemonView extends LocationArea {
                location: LocationInVersionGroup.PokemonView
            }
        }
    }
}
