// These must be kept in sync with the API's InternalLinkResolver

export const dexRouteBase = '/dex/:version';
export const ControllerRouteBases = {
    FRONT: '/',
    ABILITY: `${dexRouteBase}/ability`,
    ITEM: `${dexRouteBase}/item`,
    LOCATION: `${dexRouteBase}/location`,
    MOVE: `${dexRouteBase}/move`,
    NATURE: `${dexRouteBase}/nature`,
    POKEMON: `${dexRouteBase}/pokemon`,
    TYPE: `${dexRouteBase}/type`,
    TOOLS: `${dexRouteBase}/tools`,
} as const;

export const Routes = {
    FRONT_FRONT: ControllerRouteBases.FRONT,
    FRONT_CREDITS: '/about/credits',

    ABILITY_INDEX: ControllerRouteBases.ABILITY,
    ABILITY_VIEW: `${ControllerRouteBases.ABILITY}/:ability`,

    ITEM_INDEX: ControllerRouteBases.ITEM,
    ITEM_VIEW: `${ControllerRouteBases.ITEM}/:item`,

    LOCATION_INDEX: ControllerRouteBases.LOCATION,
    LOCATION_VIEW: `${ControllerRouteBases.LOCATION}/:location`,

    MOVE_INDEX: ControllerRouteBases.MOVE,
    MOVE_VIEW: `${ControllerRouteBases.MOVE}/:move`,

    NATURE_INDEX: ControllerRouteBases.NATURE,
    NATURE_VIEW: `${ControllerRouteBases.NATURE}/:nature`,

    POKEMON_INDEX: ControllerRouteBases.POKEMON,
    POKEMON_VIEW: `${ControllerRouteBases.POKEMON}/:species/:pokemon?/:form?`,

    TYPE_INDEX: ControllerRouteBases.TYPE,
    TYPE_VIEW: `${ControllerRouteBases.TYPE}/:type`,

    TOOLS_CAPTURE_RATE: `${ControllerRouteBases.TOOLS}/capture_rate`,

    SEARCH: `${dexRouteBase}/search/:query?`,
};

export namespace RouteParams {
    interface RouteParamsBase {
        [key: string]: any
    }

    interface DexRouteBase extends RouteParamsBase {
        version: string
    }

    export namespace Front {
        export type Front = RouteParamsBase;

        export type Credits = RouteParamsBase;
    }
    export namespace Ability {
        export type Index = DexRouteBase;

        export interface View extends DexRouteBase {
            ability: string
        }
    }
    export namespace Item {
        export type Index = DexRouteBase;

        export interface View extends DexRouteBase {
            item: string
        }
    }
    export namespace Location {
        export type Index = {};

        export interface View extends DexRouteBase {
            location: string
        }
    }
    export namespace Move {
        export type Index = DexRouteBase;

        export interface View extends DexRouteBase {
            move: string
        }
    }
    export namespace Nature {
        export type Index = DexRouteBase;

        export interface View extends DexRouteBase {
            nature: string
        }
    }
    export namespace Pokemon {
        export type Index = DexRouteBase;

        interface ViewSpecies extends DexRouteBase {
            species: string
        }

        interface ViewPokemon extends ViewSpecies {
            pokemon: string
        }

        interface ViewForm extends ViewPokemon {
            form: string
        }

        export type View = ViewSpecies | ViewPokemon | ViewForm;
    }

    export namespace Type {
        export type Index = DexRouteBase;

        export interface View extends DexRouteBase {
            type: string
        }
    }

    export namespace Tools {
        export type CaptureRate = DexRouteBase;
    }

    export interface Search {
        query?: string
    }
}

