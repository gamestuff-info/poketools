import {useCallback, useContext, useMemo} from 'react';
import AppContext, {AppContextProps} from '../../common/Context';
import {pktQuery} from '../../common/client';
import {buildOrderParams, CellRenderMap, DataTableColumnOptions} from '../../common/components/DataTable';
import {PokemonTableRecord} from '../PokemonTable';
import ItemTable, {ItemTableRecord} from '../../item/ItemTable';
import RadialGauge from '../../common/components/gauge/RadialGauge';

const sortFieldMap = {
    name: 'pokemon.name',
    types: 'pokemon.types.type.position',
    abilities: 'pokemon.abilities.ability.name',
    hp: 'pokemon.hp.baseValue',
    attack: 'pokemon.attack.baseValue',
    defense: 'pokemon.defense.baseValue',
    specialAttack: 'pokemon.specialAttack.baseValue',
    specialDefense: 'pokemon.specialDefense.baseValue',
    special: 'pokemon.special.baseValue',
    speed: 'pokemon.speed.baseValue',
    statTotal: 'pokemon.statTotal',
};

export default function PokemonWildHeldItemsTable(props: { pokemon: ApiRecord.Pokemon.Pokemon.PokemonView }) {
    const {pokemon} = props;
    const {id: pokemonId} = pokemon;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    const query = useCallback((queryPageIndex, queryPageSize, querySortBy) => {
        const params = Object.assign({}, {
            version: currentVersion.id,
            pokemon: pokemonId,
            page: queryPageIndex + 1,
            itemsPerPage: queryPageSize,
            groups: ['pokemon_view'],
        }, buildOrderParams(querySortBy, sortFieldMap));
        return pktQuery<ApiRecord.HydraCollection<PokemonTableRecord>>('pokemon_wild_held_items', params, currentVersion);
    }, [pokemonId, currentVersion]);

    const columnsCallback = useCallback((columns: Map<string, DataTableColumnOptions<ItemTableRecord>>) => {
        const addOns: Map<string, DataTableColumnOptions<ItemTableRecord>> = new Map();
        addOns.set('chance', {
            Header: 'Chance',
            accessor: (row) => row.rate,
        });
        return new Map([...addOns, ...columns]);
    }, []);

    const itemAccessor = useCallback((row: ApiRecord.Pokemon.PokemonWildHeldItem.PokemonView) => row.item, []);

    const cellRender: CellRenderMap<ApiRecord.Pokemon.PokemonWildHeldItem.PokemonView> = useMemo(() => ({
        chance: (cell) => (cell.value > 0 ? (<RadialGauge value={cell.value}/>) : (<>*</>)),
    }), []);

    return (
        <ItemTable query={query}
                   itemAccessor={itemAccessor}
                   columnsCallback={columnsCallback}
                   cellRender={cellRender}
        />
    );
}
