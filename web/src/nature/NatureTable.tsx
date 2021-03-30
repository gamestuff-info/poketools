import DataTable, {
    buildOrderParams,
    CellPropsMap,
    CellRenderMap,
    ColumnPropsMap,
    DataTableColumnOptions,
    DataTableOptions,
    ExtendableDataTableProps,
    SortBy,
} from '../common/components/DataTable';
import React, {useCallback, useContext, useMemo, useReducer} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../common/components/Flashes';
import {generatePath, Link} from 'react-router-dom';
import {Routes} from '../routes';
import TypeLabel from '../type/TypeLabel';
import versionHasContests from '../common/versionHasContests';

export type NatureTableRecord = ApiRecord.Nature.Nature & ApiRecord.Record;

interface NatureTableProps extends ExtendableDataTableProps<NatureTableRecord, ApiRecord.Nature.Nature> {
}

interface NatureTableState {
    data?: Array<ApiRecord.Nature.Nature.NatureIndex>
    loading: boolean
    pageCount?: number
}

export const sortFieldMap = {
    statIncreased: 'statIncreased.position',
    statDecreased: 'statDecreased.position',
    flavorLikes: 'flavorLikes.name',
    flavorHates: 'flavorHates.name',
    contestIncreased: 'flavorLikes.contestType.position',
    contestDecreased: 'flavorHates.contestType.position',
};

const defaultItemAccessor = (row: NatureTableRecord) => row;

export default function NatureTable(props: NatureTableProps) {
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const {query: customQuery, columnsCallback, columnProps: customColumnProps, cellRender: customCellRender} = props;
    const [state, setState] = useReducer((state: NatureTableState, newState: Partial<NatureTableState>) => ({...state, ...newState}), {
        loading: false,
    } as NatureTableState);
    const hasContests = useMemo(() => versionHasContests(currentVersion), [currentVersion]);

    const itemAccessor = props.itemAccessor ?? defaultItemAccessor;

    const columns = useMemo(() => {
        const useColumns = new Map<string, DataTableColumnOptions<ApiRecord.Nature.Nature.NatureIndex>>();
        useColumns.set('name', {
            Header: 'Name',
            accessor: (row) => itemAccessor(row).name,
        });
        useColumns.set('statIncreased', {
            Header: '+10%',
            accessor: (row) => itemAccessor(row).statIncreased.name,
        });
        useColumns.set('statDecreased', {
            // Minus sign
            Header: '−10%',
            accessor: (row) => itemAccessor(row).statDecreased.name,
        });
        useColumns.set('flavorLikes', {
            Header: 'Likes',
            accessor: (row) => itemAccessor(row).flavorLikes.name,
        });
        if (hasContests) {
            useColumns.set('contestIncreased', {
                Header: 'Contest +',
                accessor: (row) => itemAccessor(row).flavorLikes.contestType,
            });
        }
        useColumns.set('flavorHates', {
            Header: 'Hates',
            accessor: (row) => itemAccessor(row).flavorHates.name,
        });
        if (hasContests) {
            useColumns.set('contestDecreased', {
                // Minus sign
                Header: 'Contest −',
                accessor: (row) => itemAccessor(row).flavorHates.contestType,
            });
        }
        if (columnsCallback) {
            return columnsCallback(useColumns);
        }
        return useColumns;
    }, [hasContests, itemAccessor, columnsCallback]);

    const tableOptions: Partial<DataTableOptions<ApiRecord.Nature.Nature.NatureIndex>> = {
        pageCount: state.pageCount ?? -1,
        manualPagination: true,
        manualSortBy: true,
        initialState: {
            pageIndex: 0,
            pageSize: 10,
            sortBy: [{id: 'statIncreased'}, {id: 'statDecreased'}],
        },
    };

    const fetchData = useCallback((newPageIndex: number, newPageSize: number, sortBy: SortBy) => {
        let query = customQuery;
        if (!query) {
            query = (queryPageIndex, queryPageSize, querySortBy) => {
                const params = Object.assign({}, {
                    page: queryPageIndex + 1,
                    itemsPerPage: queryPageSize,
                    groups: ['nature_index'],
                }, buildOrderParams(querySortBy, sortFieldMap));
                return pktQuery<ApiRecord.HydraCollection<ApiRecord.Nature.Nature>>('natures', params, currentVersion);
            };
        }
        query(newPageIndex, newPageSize, sortBy).then((response) => {
            setState({
                loading: false,
                data: response.data['hydra:member'],
                pageCount: Math.ceil(response.data['hydra:totalItems'] / newPageSize),
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading Natures.'}]);
        });
        setState({loading: true});
    }, [currentVersion, setFlashes, customQuery]);

    const columnProps = useMemo(() => (Object.assign({}, {
        name: {className: 'text-nowrap'},
    } as ColumnPropsMap<NatureTableRecord>, customColumnProps ?? {})), [customColumnProps]);

    const textMutedForNeutralNature = useCallback((cell) => ({className: cell.row.original.neutral ? 'text-muted' : null}), []);
    const cellProps: CellPropsMap<ApiRecord.Nature.Nature.NatureIndex> = useMemo(() => ({
        statIncreased: textMutedForNeutralNature,
        statDecreased: textMutedForNeutralNature,
        flavorLikes: textMutedForNeutralNature,
        flavorHates: textMutedForNeutralNature,
        contestIncreased: textMutedForNeutralNature,
        contestDecreased: textMutedForNeutralNature,
    }), [textMutedForNeutralNature]);

    const cellRender = useMemo(() => (Object.assign({}, {
        name: cell => (
            <Link to={generatePath(Routes.NATURE_VIEW, {
                version: currentVersion.slug,
                nature: itemAccessor(cell.row.original).slug,
            })}>
                {cell.value}
            </Link>
        ),
        contestIncreased: cell => (
            <TypeLabel type={cell.value}/>
        ),
        contestDecreased: cell => (
            <TypeLabel type={cell.value}/>
        ),
    } as CellRenderMap<NatureTableRecord>, customCellRender ?? {})), [currentVersion, itemAccessor, customCellRender]);

    return (
        <DataTable columns={columns}
                   data={state.data}
                   options={tableOptions}
                   fetchData={fetchData}
                   fetchingNewData={state.loading}
                   columnProps={columnProps}
                   cellProps={cellProps}
                   cellRender={cellRender}
        />
    );
}
