import {useCallback, useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {buildOrderParams} from '../common/components/DataTable';
import PokemonTable, {PokemonTableRecord, sortFieldMap} from '../pokemon/PokemonTable';
import {pktQuery} from '../common/client';

export default function TypePokemonTable(props: { type: ApiRecord.Type.Type }) {
    const {type} = props;
    const {id: typeId} = type;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    const query = useCallback((queryPageIndex, queryPageSize, querySortBy) => {
        const params = Object.assign({}, {
            'species.versionGroup': currentVersion.versionGroup,
            'types.type': typeId,
            page: queryPageIndex + 1,
            itemsPerPage: queryPageSize,
        }, buildOrderParams(querySortBy, sortFieldMap));
        return pktQuery<ApiRecord.HydraCollection<PokemonTableRecord>>('pokemon', params, currentVersion);
    }, [typeId, currentVersion]);

    return (
        <PokemonTable query={query}/>
    );
}
