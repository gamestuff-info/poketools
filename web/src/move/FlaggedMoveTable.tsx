import React, {useCallback, useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {buildOrderParams} from '../common/components/DataTable';
import MoveTable, {sortFieldMap as moveTableSortFieldMap} from './MoveTable';
import {pktQuery} from '../common/client';

/**
 * Moves with a flag
 */
export default function FlaggedMoveTable(props: { flagSlug: string }) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const {flagSlug} = props;
    const query = useCallback((pageIndex, pageSize, sortBy) => {
        const params = Object.assign({}, {
            versionGroup: currentVersion.versionGroup,
            'flags.slug': flagSlug,
            page: pageIndex + 1,
            itemsPerPage: pageSize,
        }, buildOrderParams(sortBy, moveTableSortFieldMap));
        return pktQuery<ApiRecord.HydraCollection<ApiRecord.Move.MoveInVersionGroup>>('move_in_version_groups', params, currentVersion);
    }, [flagSlug, currentVersion]);

    return (
        <MoveTable query={query}/>
    );
}
