import ItemTable, {ItemTableRecord} from '../item/ItemTable';
import {buildOrderParams, CellRenderMap, ColumnPropsMap, DataTableColumnOptions} from '../common/components/DataTable';
import {sortFieldMap} from '../move/MoveTable';
import {pktQuery} from '../common/client';
import {useCallback, useContext, useMemo} from 'react';
import AppContext, {AppContextProps} from '../common/Context';

export default function ShopItemTable(props: { shop: ApiRecord.Location.Shop.LocationView }) {
    const {shop} = props;
    const {id: shopId} = shop;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    const query = useCallback((queryPageIndex, queryPageSize, querySortBy) => {
        const params = Object.assign({}, {
            page: queryPageIndex + 1,
            itemsPerPage: queryPageSize,
            groups: ['location_view'],
        }, buildOrderParams(querySortBy, sortFieldMap));
        return pktQuery<ApiRecord.HydraCollection<ItemTableRecord>>(`shops/${shopId}/items`, params, currentVersion);
    }, [shopId, currentVersion]);

    const columnsCallback = useCallback((columns: Map<string, DataTableColumnOptions<ItemTableRecord>>) => {
        return new Map(
            [
                ['price', {
                    Header: 'Price',
                    accessor: 'buy',
                }],
                ...columns,
            ],
        );
    }, []);

    const itemAccessor = useCallback((row: ApiRecord.Item.ShopItem.LocationView) => row.item, []);

    const columnProps: ColumnPropsMap<ApiRecord.Item.ShopItem.LocationView> = useMemo(() => ({
        price: {className: 'pkt-text'},
    }), []);

    const cellRender: CellRenderMap<ApiRecord.Item.ShopItem.LocationView> = useMemo(() => ({
        price: (cell) => <>${cell.value}</>,
    }), []);

    return (
        <ItemTable query={query}
                   columnsCallback={columnsCallback}
                   itemAccessor={itemAccessor}
                   columnProps={columnProps}
                   cellRender={cellRender}
        />
    );
}
