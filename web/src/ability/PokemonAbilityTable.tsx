import {useCallback, useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import PokemonTable, {sortFieldMap as pokemonTableSortFieldMap} from '../pokemon/PokemonTable';
import {buildOrderParams} from '../common/components/DataTable';
import {pktQuery} from '../common/client';

export default function PokemonAbilityTable(props: { ability: ApiRecord.Ability.AbilityInVersionGroup }) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const {ability} = props;
    const query = useCallback((pageIndex, pageSize, sortBy) => {
        const params = Object.assign({}, {
            'species.versionGroup': currentVersion.versionGroup,
            'abilities.ability': ability.id,
            page: pageIndex + 1,
            itemsPerPage: pageSize,
        }, buildOrderParams(sortBy, pokemonTableSortFieldMap));
        return pktQuery<ApiRecord.HydraCollection<ApiRecord.Pokemon.Pokemon>>('pokemon', params, currentVersion);
    }, [ability, currentVersion]);

    return (<PokemonTable query={query}/>);
}
