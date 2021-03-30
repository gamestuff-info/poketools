import {useCallback, useContext, useMemo} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {buildOrderParams} from '../common/components/DataTable';
import PokemonTable, {PokemonTableRecord, sortFieldMap} from '../pokemon/PokemonTable';
import {pktQuery} from '../common/client';

export default function PokemonBreedingTable(props: { pokemon: ApiRecord.Pokemon.Pokemon & Pick<ApiRecord.Pokemon.Pokemon.PokemonView, 'eggGroups'> }) {
    const {pokemon} = props;
    const {eggGroups} = pokemon;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const eggGroupIds = useMemo(() => eggGroups.map(eggGroup => eggGroup.id), [eggGroups]);

    const query = useCallback((queryPageIndex, queryPageSize, querySortBy) => {
        const params = Object.assign({}, {
            'species.versionGroup': currentVersion.versionGroup,
            eggGroups: eggGroupIds,
            mega: 0,
            baby: 0,
            page: queryPageIndex + 1,
            itemsPerPage: queryPageSize,
        }, buildOrderParams(querySortBy, sortFieldMap));
        return pktQuery<ApiRecord.HydraCollection<PokemonTableRecord>>('pokemon', params, currentVersion);
    }, [eggGroupIds, currentVersion]);

    return (
        <PokemonTable query={query}/>
    );
}
