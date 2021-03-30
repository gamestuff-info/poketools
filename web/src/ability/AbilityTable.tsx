import React, {useCallback, useContext, useMemo, useReducer} from 'react';
import DataTable, {
    buildOrderParams,
    CellRenderMap,
    ColumnPropsMap,
    DataTableColumnOptions,
    DataTableOptions,
    SortBy,
} from '../common/components/DataTable';
import {pktQuery} from '../common/client';
import {FlashSeverity} from '../common/components/Flashes';
import {AxiosError} from 'axios';
import {generatePath, Link} from 'react-router-dom';
import {Routes} from '../routes';
import PktMarkdown from '../common/components/PktMarkdown';
import AppContext, {AppContextProps} from '../common/Context';

interface AbilityTableProps {
}

interface AbilityTableState {
    data?: Array<ApiRecord.Ability.AbilityInVersionGroup>
    loading: boolean
    pageCount?: number
}

export default function AbilityTable(props: AbilityTableProps) {
    const columns: Array<DataTableColumnOptions<ApiRecord.Ability.AbilityInVersionGroup>> = [
        {Header: 'Name', accessor: 'name'},
        {Header: 'Description', accessor: 'shortDescription', disableSortBy: true},
    ];
    const [state, setState] = useReducer((state: AbilityTableState, newState: Partial<AbilityTableState>) => ({...state, ...newState}), {
        loading: false,
    } as AbilityTableState);
    const tableOptions: Partial<DataTableOptions<ApiRecord.Ability.AbilityInVersionGroup>> = {
        pageCount: state.pageCount ?? -1,
        manualPagination: true,
        manualSortBy: true,
        initialState: {
            pageIndex: 0,
            pageSize: 10,
            sortBy: [{id: 'name'}],
        },
    };
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const fetchData = useCallback((newPageIndex: number, newPageSize: number, sortBy: SortBy) => {
        const params = Object.assign({}, {
            versionGroup: (currentVersion as ApiRecord.Version).versionGroup,
            page: newPageIndex + 1,
            itemsPerPage: newPageSize,
        }, buildOrderParams(sortBy));
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Ability.AbilityInVersionGroup>>('ability_in_version_groups', params, currentVersion)
            .then((response) => {
                setState({
                    loading: false,
                    data: response.data['hydra:member'],
                    pageCount: Math.ceil(response.data['hydra:totalItems'] / newPageSize),
                });
            })
            .catch((error: AxiosError) => {
                console.log(error.message);
                setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading abilities.'}]);
            });
        setState({loading: true});
    }, [currentVersion, setFlashes]);
    const columnProps: ColumnPropsMap<ApiRecord.Ability.AbilityInVersionGroup> = useMemo(() => ({
        name: {className: 'text-nowrap'},
    }), []);
    const cellRender: CellRenderMap<ApiRecord.Ability.AbilityInVersionGroup> = useMemo(() => ({
        name: (cell) => (<Link to={generatePath(Routes.ABILITY_VIEW, {
            version: currentVersion.slug,
            ability: cell.row.original.slug,
        })}>{cell.value}</Link>),
        shortDescription: (cell) => (<PktMarkdown>{cell.value}</PktMarkdown>),
    }), [currentVersion]);
    return (
        <DataTable columns={columns}
                   data={state.data}
                   options={tableOptions}
                   fetchData={fetchData}
                   fetchingNewData={state.loading}
                   columnProps={columnProps}
                   cellRender={cellRender}
        />
    );
}
