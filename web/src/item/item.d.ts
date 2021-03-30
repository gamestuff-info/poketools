namespace ApiRecord {
    namespace Item {
        interface ItemCategory extends ApiRecord.Entity, EntityHasNameAndSlug {
        }

        interface ItemPocket extends ApiRecord.Entity,
            EntityHasIcon,
            EntityHasId,
            EntityHasNameAndSlug,
            EntityIsSortable {
        }

        interface ItemFlingEffect extends ApiRecord.Entity,
            EntityHasDescription {
        }

        interface ItemFlag extends ApiRecord.Entity,
            EntityHasDescription,
            EntityHasNameAndSlug {
        }

        interface BerryFirmness extends ApiRecord.Record,
            EntityHasNameAndSlug {
        }

        interface BerryFlavor extends ApiRecord.Entity,
            EntityHasNameAndSlug {
        }

        interface BerryFlavorLevel extends ApiRecord.Record {
            flavor: BerryFlavor
            level: number
        }

        interface Berry extends ApiRecord.Entity,
            EntityHasFlavorText {
            number?: number
            firmness: BerryFirmness
            naturalGiftPower?: number
            naturalGiftType?: ApiRecord.Type.Type
            sizeMillimeters: number
            harvest: string
            growthTimeSeconds: number
            water?: number
            weeds?: number
            pests?: number
            smoothness?: number
            flavors: Array<BerryFlavorLevel>
        }

        interface Machine extends ApiRecord.Entity {
            type: 'TM' | 'HM'
            number: number
        }

        interface Decoration extends ApiRecord.Record {
            width: number
            height: number
        }

        interface ShopItem extends ApiRecord.Entity,
            EntityHasId,
            EntityIsSortable {
            buy?: number
        }

        namespace ShopItem {
            interface ItemView extends ShopItem {
                shop: Location.ItemShop
            }
        }

        interface ItemInVersionGroup extends ApiRecord.Entity,
            EntityGroupedByVersionGroup,
            EntityHasDescription,
            EntityHasFlavorText,
            EntityHasIcon,
            EntityHasId,
            EntityHasNameAndSlug {
            buy?: number
            sell?: number
        }

        namespace ItemInVersionGroup {
            interface ItemView extends ItemInVersionGroup {
                category: ItemCategory
                pocket: ItemPocket
                flingEffect?: ItemFlingEffect
                flingPower?: number
                flags: Array<ItemFlag>
                berry?: Berry
                machine?: Machine
                decoration?: Decoration
            }
        }
    }

    namespace Pokemon {
        namespace PokemonWildHeldItem {
            interface ItemView extends PokemonWildHeldItem {
                pokemon: Pokemon
            }
        }
    }

    namespace Location {
        namespace Shop {
            interface ItemView extends Shop {
                locationArea: LocationArea.ItemView
            }
        }

        namespace LocationArea {
            interface ItemView extends LocationArea {
                location?: LocationInVersionGroup
                treeRoot: ItemLocationArea | string
            }
        }
    }
}
