namespace ApiRecord {
    namespace Item {
        namespace ItemInVersionGroup {
            type CaptureRate = Pick<ItemInVersionGroup.ItemView, '@id' | 'id' | 'name' | 'slug' | 'category' | 'icon'>;
        }
    }

    namespace Pokemon {
        namespace Pokemon {
            interface CaptureRate extends Pick<Pokemon.PokemonView, '@id' | 'id' | 'name' | 'slug' | 'speciesSlug' | 'defaultForm' | 'captureRate' | 'weightGrams' | 'speed' | 'types'> {
                moonStone: boolean
            }
        }
    }
}
