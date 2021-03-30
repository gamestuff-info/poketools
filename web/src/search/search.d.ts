namespace ApiRecord {
    namespace Search {
        interface SearchResultBase {
            id: number
            label: string
        }

        interface SearchResultPokemon extends SearchResultBase {
            type: 'pokemon'
            result: ApiRecord.Pokemon.Pokemon
        }

        interface SearchResultMove extends SearchResultBase {
            type: 'move'
            result: ApiRecord.Move.MoveInVersionGroup
        }

        interface SearchResultType extends SearchResultBase {
            type: 'type'
            result: ApiRecord.Type.Type
        }

        interface SearchResultItem extends SearchResultBase {
            type: 'item'
            result: ApiRecord.Item.ItemInVersionGroup
        }

        interface SearchResultLocation extends SearchResultBase {
            type: 'location'
            result: ApiRecord.Location.LocationInVersionGroup.SearchResult
        }

        interface SearchResultNature extends SearchResultBase {
            type: 'nature'
            result: ApiRecord.Nature.Nature
        }

        interface SearchResultAbility extends SearchResultBase {
            type: 'ability'
            result: ApiRecord.Ability.AbilityInVersionGroup
        }

        type SearchResult =
            SearchResultPokemon
            | SearchResultMove
            | SearchResultType
            | SearchResultItem
            | SearchResultLocation
            | SearchResultNature
            | SearchResultAbility;
    }

    namespace Location {
        namespace LocationInVersionGroup {
            interface SearchResult extends LocationInVersionGroup, Pick<LocationInVersionGroup.LocationView, 'effectiveMap'> {
                regionMap?: RegionMap
            }
        }
    }
}
