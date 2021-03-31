import {useCallback, useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {buildOrderParams} from '../common/components/DataTable';
import PokemonTable, {PokemonTableRecord, sortFieldMap} from '../pokemon/PokemonTable';
import {pktQuery} from '../common/client';

export default function MachinePokemonTable(props: { itemSlug: string }) {
    const {itemSlug} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    const query = useCallback((queryPageIndex, queryPageSize, querySortBy) => {
        const params = Object.assign({}, {
            'machine.slug': itemSlug,
            'pokemon.species.versionGroup': currentVersion.versionGroup,
            page: queryPageIndex + 1,
            itemsPerPage: queryPageSize,
            groups: ['move_view'],
        }, buildOrderParams(querySortBy, sortFieldMap));
        return pktQuery<ApiRecord.HydraCollection<PokemonTableRecord>>('pokemon_moves', params, currentVersion);
    }, [itemSlug, currentVersion]);

    const itemAccessor = useCallback((row: ApiRecord.Pokemon.PokemonMove.MoveView) => row.pokemon, []);

    return (
        <PokemonTable query={query}
                      itemAccessor={itemAccessor}
        />
    );
}
