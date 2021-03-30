namespace ApiRecord {
    namespace Location {
        interface RegionMap extends ApiRecord.Entity,
            EntityHasId,
            MediaEntity,
            EntityHasNameAndSlug,
            EntityIsSortable {
            width: number
            height: number
        }

        interface Region extends ApiRecord.Entity,
            EntityHasId,
            EntityGroupedByVersionGroup,
            EntityHasNameAndSlug,
            EntityIsSortable {
        }

        namespace Region {
            interface LocationIndex extends Region {
                maps: Array<RegionMap>
            }
        }

        interface Shop extends ApiRecord.Entity,
            EntityHasDefault,
            EntityHasId,
            EntityHasNameAndSlug {
        }

        namespace Shop {
            interface LocationView extends Shop {
                // items: Array<ApiRecord.Item.ShopItem.LocationView>
            }
        }

        interface LocationArea extends ApiRecord.Entity,
            EntityHasDefault,
            EntityHasId,
            EntityHasNameAndSlug,
            EntityIsSortable {
        }

        namespace LocationArea {
            interface LocationView extends LocationArea {
                shops: Array<ApiRecord.Location.Shop.LocationView>
                treeChildren: Array<ApiRecord.Location.LocationArea.LocationView>
            }
        }

        interface LocationMap {
            map: string
            overlay?: string
            zIndex: number
        }

        interface LocationInVersionGroup extends ApiRecord.Entity,
            EntityGroupedByVersionGroup,
            EntityHasDescription,
            EntityHasId,
            EntityHasNameAndSlug {
            region: string
            superLocation?: string
            subLocations: Array<LocationInVersionGroup>
        }

        namespace LocationInVersionGroup {
            interface WithLocationMap {
                /** This location's map, if available, the map of a parent location, or undefined if no maps apply. */
                effectiveMap?: LocationMap
                map?: LocationMap
            }

            interface LocationIndex extends LocationInVersionGroup,
                WithLocationMap {
            }

            interface LocationView extends LocationInVersionGroup,
                WithLocationMap {
                areas: Array<LocationArea.LocationView>
            }
        }
    }

    namespace Item {
        namespace ShopItem {
            interface LocationView extends ShopItem {
                item: ItemInVersionGroup
            }
        }
    }
}
