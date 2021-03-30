import DataTable, {
    buildOrderParams,
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
import PktMarkdown from '../common/components/PktMarkdown';
import TypeLabel from '../type/TypeLabel';
import DamageClassLabel from './DamageClassLabel';
import versionHasContests from '../common/versionHasContests';

export type MoveTableRecord = ApiRecord.Move.MoveInVersionGroup & ApiRecord.Record;

interface MoveTableProps extends ExtendableDataTableProps<MoveTableRecord, ApiRecord.Move.MoveInVersionGroup> {
}

interface MoveTableState {
    data?: Array<MoveTableRecord>
    loading: boolean
    pageCount?: number
}

export const sortFieldMap = {
    type: 'type.position',
    contestType: 'contestType.position',
    damageClass: 'damageClass.position',
};

const defaultItemAccessor = (row: MoveTableRecord) => row;

export default function MoveTable(props: MoveTableProps) {
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const {
        query: customQuery,
        columnsCallback,
        columnProps: customColumnProps,
        cellProps: customCellProps,
        cellRender: customCellRender,
    } = props;
    const [state, setState] = useReducer((state: MoveTableState, newState: Partial<MoveTableState>) => ({...state, ...newState}), {
        loading: false,
    } as MoveTableState);
    const hasContests = useMemo(() => versionHasContests(currentVersion), [currentVersion]);

    const itemAccessor = props.itemAccessor ?? defaultItemAccessor;

    const columns = useMemo(() => {
        const useColumns = new Map<string, DataTableColumnOptions<MoveTableRecord>>();
        useColumns.set('name', {
            Header: 'Name',
            accessor: (row) => itemAccessor(row).name,
        });
        useColumns.set('type', {
            Header: 'Type',
            accessor: (row) => itemAccessor(row).type,
        });
        if (hasContests) {
            useColumns.set('contestType', {
                Header: 'Contest',
                accessor: (row) => itemAccessor(row).contestType,
            });
        }
        useColumns.set('damageClass', {
            Header: 'Class',
            accessor: (row) => itemAccessor(row).effectiveDamageClass,
        });
        useColumns.set('pp', {
            Header: 'PP',
            accessor: (row) => itemAccessor(row).pp,
        });
        useColumns.set('power', {
            Header: 'Pwr',
            accessor: (row) => itemAccessor(row).power ?? '',
        });
        useColumns.set('accuracy', {
            Header: 'Acc',
            accessor: (row) => itemAccessor(row).accuracy ?? '',
        });
        useColumns.set('description', {
            Header: 'Description',
            accessor: (row) => itemAccessor(row).effect.shortDescription,
            disableSortBy: true,
        });
        if (columnsCallback) {
            return columnsCallback(useColumns);
        }
        return useColumns;
    }, [hasContests, columnsCallback, itemAccessor]);

    const tableOptions: Partial<DataTableOptions<ApiRecord.Move.MoveInVersionGroup>> = {
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
                }, buildOrderParams(querySortBy, sortFieldMap));
                return pktQuery<ApiRecord.HydraCollection<ApiRecord.Move.MoveInVersionGroup>>('move_in_version_groups', params, currentVersion);
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
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading moves.'}]);
        });
        setState({loading: true});
    }, [currentVersion, setFlashes, customQuery]);

    const columnProps = useMemo(() => (Object.assign({}, {
        name: {className: 'text-nowrap'},
    } as ColumnPropsMap<MoveTableRecord>, customColumnProps ?? {})), [customColumnProps]);

    const cellRender = useMemo(() => (Object.assign({}, {
        name: cell => (
            <Link to={generatePath(Routes.MOVE_VIEW, {
                version: currentVersion.slug,
                move: itemAccessor(cell.row.original).slug,
            })}>
                {cell.value}
            </Link>
        ),
        type: cell => (<TypeLabel type={cell.value}/>),
        contestType: cell => (<TypeLabel type={cell.value}/>),
        damageClass: cell => (<DamageClassLabel damageClass={cell.value}/>),
        description: cell => (
            <PktMarkdown>
                {cell.value.replaceAll('$effect_chance', String(itemAccessor(cell.row.original).effectChance ?? 0))}
            </PktMarkdown>
        ),
    } as CellRenderMap<MoveTableRecord>, customCellRender ?? {})), [currentVersion, itemAccessor, customCellRender]);

    return (
        <DataTable columns={columns}
                   data={state.data}
                   options={tableOptions}
                   fetchData={fetchData}
                   fetchingNewData={state.loading}
                   columnProps={columnProps}
                   cellProps={customCellProps}
                   cellRender={cellRender}
        />
    );
}
