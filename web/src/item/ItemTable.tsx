import DataTable, {
    buildOrderParams,
    CellRenderMap,
    ColumnPropsMap,
    DataTableColumnOptions,
    DataTableOptions,
    ExtendableDataTableProps,
    SortBy,
} from '../common/components/DataTable';
import {AxiosError} from 'axios';
import React, {useCallback, useContext, useMemo, useReducer} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {pktQuery} from '../common/client';
import {FlashSeverity} from '../common/components/Flashes';
import PktMarkdown from '../common/components/PktMarkdown';
import ItemLabel from './ItemLabel';

export type ItemTableRecord = ApiRecord.Item.ItemInVersionGroup & ApiRecord.Record;

interface ItemTableProps extends ExtendableDataTableProps<ItemTableRecord, ApiRecord.Item.ItemInVersionGroup> {
}

interface ItemTableState {
    data?: Array<ItemTableRecord>
    loading: boolean
    pageCount?: number
}

const defaultItemAccessor = (row: ItemTableRecord) => row;

export default function ItemTable(props: ItemTableProps) {
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const {query: customQuery, columnsCallback, columnProps: customColumnProps, cellRender: customCellRender} = props;
    const [state, setState] = useReducer((state: ItemTableState, newState: Partial<ItemTableState>) => ({...state, ...newState}), {
        loading: false,
    } as ItemTableState);

    const itemAccessor = props.itemAccessor ?? defaultItemAccessor;

    const columns = useMemo(() => {
        let useColumns = new Map<string, DataTableColumnOptions<ItemTableRecord>>();
        useColumns.set('name', {
            Header: 'Name',
            accessor: (row) => itemAccessor(row).name,
        });
        useColumns.set('description', {
            Header: 'Description',
            accessor: (row) => itemAccessor(row).shortDescription,
            disableSortBy: true,
        });
        if (columnsCallback) {
            return columnsCallback(useColumns);
        }
        return useColumns;
    }, [columnsCallback, itemAccessor]);

    const tableOptions: Partial<DataTableOptions<ItemTableRecord>> = {
        pageCount: state.pageCount ?? -1,
        manualPagination: true,
        manualSortBy: true,
        initialState: {
            pageIndex: 0,
            pageSize: 10,
            sortBy: [{id: 'name'}],
        },
    };

    const fetchData = useCallback((newPageIndex: number, newPageSize: number, sortBy: SortBy) => {
        let query = customQuery;
        if (!query) {
            query = (queryPageIndex, queryPageSize, querySortBy) => {
                const params = Object.assign({}, {
                    versionGroup: currentVersion.versionGroup,
                    page: queryPageIndex + 1,
                    itemsPerPage: queryPageSize,
                }, buildOrderParams(querySortBy));
                return pktQuery<ApiRecord.HydraCollection<ItemTableRecord>>('item_in_version_groups', params, currentVersion);
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
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading items.'}]);
        });
        setState({loading: true});
    }, [currentVersion, setFlashes, customQuery]);

    const columnProps: ColumnPropsMap<ItemTableRecord> = useMemo(() => (Object.assign({}, {
        name: {className: 'text-nowrap'},
    } as ColumnPropsMap<ItemTableRecord>, customColumnProps ?? {})), [customColumnProps]);

    const cellRender: CellRenderMap<ItemTableRecord> = useMemo(() => (Object.assign({}, {
        name: cell => <ItemLabel item={itemAccessor(cell.row.original)}/>,
        description: cell => (<PktMarkdown>{cell.value}</PktMarkdown>),
    } as CellRenderMap<ItemTableRecord>, customCellRender ?? {})), [customCellRender, itemAccessor]);

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
