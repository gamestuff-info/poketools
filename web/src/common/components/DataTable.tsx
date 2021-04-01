import React, {useMemo} from 'react';
import {
    Cell,
    Column,
    ColumnInstance,
    HeaderGroup,
    Row,
    TableInstance,
    TableOptions,
    TableState,
    useAsyncDebounce,
    usePagination,
    UsePaginationInstanceProps,
    UsePaginationOptions,
    UsePaginationState,
    useSortBy,
    UseSortByColumnOptions,
    UseSortByColumnProps,
    UseSortByInstanceProps,
    UseSortByOptions,
    UseSortByState,
    useTable,
} from 'react-table';
import './DataTable.scss';
import Loading from './Loading';
import {Button, ButtonGroup, Col as BsCol, Form, Table} from 'react-bootstrap';
import {
    faAngleDoubleLeft,
    faAngleDoubleRight,
    faAngleLeft,
    faAngleRight,
    faSort,
    faSortDown,
    faSortUp,
} from '@fortawesome/free-solid-svg-icons';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {AxiosResponse} from 'axios';

export type DataTableState<T extends object> = TableState<T>
    & UseSortByState<T>
    & UsePaginationState<T>;

export type DataTableOptions<T extends object> = TableOptions<T>
    & UseSortByOptions<T>
    & UsePaginationOptions<T>
    & { initialState?: Partial<DataTableState<T>> };

export type DataTableInstance<T extends object> = TableInstance<T>
    & UseSortByInstanceProps<T>
    & UsePaginationInstanceProps<T>
    & { state: DataTableState<T> };

export type DataTableColumnOptions<T extends object> = Column<T>
    & UseSortByColumnOptions<T>;

export type DataTableColumnInstance<T extends object> = ColumnInstance<T>
    & UseSortByColumnProps<T>;

export type HeaderInstance<T extends object> = HeaderGroup<T>
    & UseSortByColumnProps<T>;

enum SortDirection {
    ASC = 'asc',
    DESC = 'desc',
}

export type SortBy = Record<string, SortDirection>;
export type ColumnConfig<T extends object> = Array<DataTableColumnOptions<T>>
    | Map<string, DataTableColumnOptions<T>>;
export type HeaderPropsMap<T extends object> = Record<string, Record<string, any> | ((column: Column<T>) => Record<string, any>)>;
export type ColumnPropsMap<T extends object> = Record<string, Record<string, any> | ((column: Column<T>) => Record<string, any>)>;
export type RowPropsGetter<T extends object> = Record<string, any> | ((row: Row<T>) => Record<string, any>);
export type CellPropsMap<T extends object> = Record<string, Record<string, any> | ((cell: Cell<T>) => Record<string, any>)>;
export type CellRenderMap<T extends object> = Record<string, (cell: Cell<T>) => JSX.Element>;
export type FetchDataCallback = (pageIndex: number, pageSize: number, sortBy: SortBy) => void;

// Extendable tables
export type QueryCallback<T extends object> = (newPageIndex: number, newPageSize: number, sortBy: SortBy) => Promise<AxiosResponse<ApiRecord.HydraCollection<T>>>;
export type ColumnsCallback<T extends object> = (columns: Map<string, DataTableColumnOptions<T>>) => ColumnConfig<T>;
export type TableOptionsCallback<T extends object> = (options: Partial<DataTableOptions<T>>) => Partial<DataTableOptions<T>>;

export interface ExtendableDataTableProps<T extends object, BaseT extends object> {
    query?: QueryCallback<T>
    /** Modify the columns in the table.  Must use `useCallback()` */
    columnsCallback?: ColumnsCallback<T>
    /** Adjust column props.  Must be memoized. */
    columnProps?: ColumnPropsMap<T>
    /** Adjust cell props.  Must be memoized. */
    cellProps?: CellPropsMap<T>
    /** Adjust how cells are rendered.  Must be memoized. */
    cellRender?: CellRenderMap<T>
    /** When overriding the query source to fetch an association record, also provide this function that can fetch an
     *  item.  Must use `useCallback()` */
    itemAccessor?: (row: T) => BaseT
}

interface DataTableProps<T extends object> {
    /** Columns */
    columns: ColumnConfig<T>
    /** Dataset */
    data?: Array<T>
    /** Table options */
    options?: Partial<DataTableOptions<T>>
    /** Map column id to an object of props to apply to `<th>` elements */
    headerProps?: HeaderPropsMap<T>
    /** Map column id to an object of props to apply to both `<th>` and `<td>` elements */
    columnProps?: ColumnPropsMap<T>
    /** An object of props to apply to `<tr>` elements */
    rowProps?: RowPropsGetter<T>
    /** Map column id to an object of props to apply to `<td>` elements */
    cellProps?: CellPropsMap<T>
    /** Map column id to a function that renders a cell's value */
    cellRender?: CellRenderMap<T>
    /** A function to call when a change in data is requested. */
    fetchData?: FetchDataCallback
    /** When true, a loading element is shown */
    fetchingNewData?: boolean
}

export default function DataTable<T extends object = ApiRecord.Record>(props: DataTableProps<T>) {
    const defaultTableOptions: DataTableOptions<T> = {
        // Allowing both lists and maps of columns allows more extensible column configuration
        columns: useMemo(() => Array.isArray(props.columns) ? props.columns : getColumnListFromMap(props.columns), [props.columns]),
        data: props.data ?? [],
    };
    const tableOptions: DataTableOptions<T> = Object.assign({}, defaultTableOptions, props.options ?? {});
    const {
        // Table
        getTableProps,
        getTableBodyProps,
        headerGroups,
        prepareRow,
        // Pagination
        page,
        canPreviousPage,
        canNextPage,
        pageOptions,
        pageCount,
        gotoPage,
        nextPage,
        previousPage,
        setPageSize,
        state: tableState,
    } = useTable(tableOptions,
        useSortBy,
        usePagination) as DataTableInstance<T>;
    const {pageIndex, pageSize, sortBy} = tableState as DataTableState<T>;
    const fetchData = props.fetchData;
    const sortMap = useMemo(() => {
        const sorts: SortBy = {};
        for (const sort of sortBy) {
            sorts[sort.id] = sort.desc ? SortDirection.DESC : SortDirection.ASC;
        }
        return sorts;
    }, [sortBy]);
    const fetchDataDebounced = useAsyncDebounce(() => fetchData && fetchData(pageIndex, pageSize, sortMap), 100);
    React.useEffect(fetchDataDebounced, [fetchDataDebounced, pageIndex, pageSize, sortBy]);

    function Controls(props: {}) {
        return (
            <Form className="pkt-datatable-controls">
                <Form.Row className="align-items-center">
                    {/* Page size selector */}
                    <Form.Group as={BsCol} sm>
                        <Form.Label htmlFor="rowsPerPage">Rows per page</Form.Label>
                        <Form.Control
                            as="select"
                            name="rowsPerPage"
                            value={pageSize}
                            onChange={e => setPageSize(parseInt(e.target.value))}
                        >
                            {[10, 25, 50, 100].map(page_size =>
                                <option key={page_size}
                                        value={page_size}>{page_size}</option>)}
                        </Form.Control>
                    </Form.Group>

                    {/* Page selector */}
                    <Form.Group as={BsCol} sm>
                        <Form.Label htmlFor="page">Go to page</Form.Label>
                        <Form.Control
                            as="select"
                            value={pageIndex}
                            onChange={e => gotoPage(parseInt(e.target.value))}
                        >
                            {pageOptions.map(pageNum =>
                                <option key={pageNum} value={pageNum}>{pageNum + 1}</option>)}
                        </Form.Control>
                    </Form.Group>

                    {/* Next/previous buttons */}
                    <Form.Group as={BsCol} md>
                        <ButtonGroup>
                            {/* First page */}
                            <Button
                                variant="secondary"
                                title="First page"
                                disabled={!canPreviousPage}
                                onClick={() => gotoPage(0)}
                            >
                                <FontAwesomeIcon icon={faAngleDoubleLeft}/>
                            </Button>
                            {/* Previous page */}
                            <Button
                                variant="secondary"
                                title="Previous page"
                                disabled={!canPreviousPage}
                                onClick={() => previousPage()}
                            >
                                <FontAwesomeIcon icon={faAngleLeft}/>
                            </Button>
                            {/* Current page */}
                            <Button
                                variant="outline-secondary"
                                disabled
                            >
                                {pageIndex + 1} / {pageCount}
                            </Button>
                            {/* Next page */}
                            <Button
                                variant="secondary"
                                title="Next page"
                                disabled={!canNextPage}
                                onClick={() => nextPage()}
                            >
                                <FontAwesomeIcon icon={faAngleRight}/>
                            </Button>
                            {/* Last page */}
                            <Button
                                variant="secondary"
                                title="Last page"
                                disabled={!canNextPage}
                                onClick={() => gotoPage(pageCount - 1)}
                            >
                                <FontAwesomeIcon icon={faAngleDoubleRight}/>
                            </Button>
                        </ButtonGroup>
                    </Form.Group>
                </Form.Row>
            </Form>
        );
    }

    return (
        <div className="pkt-datatable-container">
            <Controls/>
            {(props.fetchingNewData || props.data === null) && (<Loading/>)}
            {props.data !== null &&
            <Table {...getTableProps()} responsive className="pkt-datatable">
                <thead>
                {headerGroups.map(headerGroup => (
                    <tr {...headerGroup.getHeaderGroupProps()}>
                        {(headerGroup.headers as Array<HeaderInstance<T>>).map((column) => {
                            return (
                                <th{...column.getHeaderProps([
                                    column.getSortByToggleProps(),
                                    getCustomColumnProps(column.id, props.headerProps, column),
                                    getCustomColumnProps(column.id, props.columnProps, column),
                                ])}>
                                    {column.render('Header')}{getSortIcon(column)}
                                </th>
                            );
                        })}
                    </tr>
                ))}
                </thead>
                <tbody {...getTableBodyProps()}>
                {flattenRows(page).map(row => {
                    prepareRow(row);
                    return (
                        <tr {...row.getRowProps([
                            getCustomProps(props.rowProps, row),
                        ])}>
                            {row.cells.filter(cell => cell.value !== undefined).map(cell => {
                                return (
                                    // The cell contents are pre-escaped and rendered on the server
                                    <td {...cell.getCellProps([
                                        getCustomColumnProps(cell.column.id, props.columnProps, cell.column),
                                        getCustomColumnProps(cell.column.id, props.cellProps, cell),
                                    ])}>
                                        {props.cellRender && cell.column.id in props.cellRender ? props.cellRender[cell.column.id](cell) : cell.value}
                                    </td>
                                );
                            })}
                        </tr>
                    );
                })}
                </tbody>
            </Table>}
            <Controls/>
        </div>
    );
}

/**
 * Insert subrows after their parents.
 * @param rows
 */
function flattenRows<T extends object>(rows: Array<Row<T>>) {
    for (let i = 0; i < rows.length; ++i) {
        if (rows[i].subRows.length > 0) {
            rows.splice(i + 1, 0, ...rows[i].subRows);
        }
    }

    return rows;
}

/**
 * Get the correct sorting icon element
 * @param column
 * @return The icon element, or null if the column cannot be sorted
 */
function getSortIcon<T extends object>(column: DataTableColumnInstance<T>) {
    if (!column.canSort) {
        return null;
    }
    const iconClass = 'pkt-table-icon';
    if (column.isSorted) {
        if (column.isSortedDesc) {
            return (<FontAwesomeIcon icon={faSortUp} className={iconClass}/>);
        } else {
            return (<FontAwesomeIcon icon={faSortDown} className={iconClass}/>);
        }
    }
    return (<FontAwesomeIcon icon={faSort} className={iconClass}/>);
}

function getCustomProps(props?: Record<string, any> | ((...params: any) => Record<string, any>), ...params: any): Record<string, any> {
    if (props) {
        if (typeof props === 'function') {
            return (props as Function)(...params);
        } else {
            return props;
        }
    }
    return {};
}

/**
 * Get requested custom props, if applicable
 * @param columnId
 * @param propsMap Map column id to either an object of params or a function that returns such an object
 * @param params The parameters to pass to the function in propsMap, if applicable
 */
function getCustomColumnProps(columnId: string, propsMap?: Record<string, Record<string, any> | ((...params: any) => Record<string, any>)>, ...params: any): Record<string, any> {
    if (propsMap && columnId in propsMap) {
        return getCustomProps(propsMap[columnId], ...params);
    }
    return {};
}

/**
 * Convert a map of columns keyed by id to a list.
 * @param columns
 */
function getColumnListFromMap<T extends object>(columns: Map<string, DataTableColumnOptions<T>>): Array<DataTableColumnOptions<T>> {
    const columnList = [];
    for (const [id, column] of columns.entries()) {
        columnList.push(Object.assign({}, column, {id: id}));
    }
    return columnList;
}

/**
 * Build an object of parameters from the provided sorts.
 * @param sortBy
 * @param sortFieldMap Map column ids from sortBy to the field to sort with
 * @param param The sorting parameter name.  Defaults to `order`, the API Platform default.
 */
export function buildOrderParams(sortBy: SortBy, sortFieldMap: Record<string, string> = {}, param: string = 'order') {
    const params: Record<string, string> = {};
    for (const [k, v] of Object.entries(sortBy)) {
        const sortField = sortFieldMap[k] ?? k;
        params[`${param}[${sortField}]`] = v.valueOf();
    }

    return params;
}
