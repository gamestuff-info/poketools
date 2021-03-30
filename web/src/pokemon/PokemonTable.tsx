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
import PokemonLabel from './PokemonLabel';
import PokemonTypeList from './PokemonTypeList';
import PokemonAbilityLabel from './PokemonAbilityLabel';

export type PokemonTableRecord = ApiRecord.Pokemon.Pokemon & ApiRecord.Record;

interface PokemonTableProps extends ExtendableDataTableProps<PokemonTableRecord, ApiRecord.Pokemon.Pokemon> {
}

interface ItemTableState {
    data?: Array<PokemonTableRecord>
    loading: boolean
    pageCount?: number
}

const defaultItemAccessor = (row: PokemonTableRecord) => row;

export const sortFieldMap = {
    types: 'types.type.position',
    abilities: 'abilities.ability.name',
    hp: 'hp.baseValue',
    attack: 'attack.baseValue',
    defense: 'defense.baseValue',
    specialAttack: 'specialAttack.baseValue',
    specialDefense: 'specialDefense.baseValue',
    special: 'special.baseValue',
    speed: 'speed.baseValue',
};

export default function PokemonTable(props: PokemonTableProps) {
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const {
        query: customQuery,
        columnsCallback,
        columnProps: customColumnProps,
        cellProps: customCellProps,
        cellRender: customCellRender,
    } = props;
    const [state, setState] = useReducer((state: ItemTableState, newState: Partial<ItemTableState>) => ({...state, ...newState}), {
        loading: false,
    } as ItemTableState);
    const hasAbilities = currentVersion.featureSlugs.includes('abilities');
    const hasSpecialStat = currentVersion.featureSlugs.includes('special-stat');

    const itemAccessor = props.itemAccessor ?? defaultItemAccessor;

    const columns = useMemo(() => {
        let useColumns = new Map<string, DataTableColumnOptions<PokemonTableRecord>>();
        useColumns.set('name', {
            Header: 'Name',
            accessor: (row) => itemAccessor(row).name,
        });
        useColumns.set('types', {
            Header: 'Type',
            accessor: (row) => itemAccessor(row).types,
        });
        if (hasAbilities) {
            useColumns.set('abilities', {
                Header: 'Abilities',
                accessor: (row) => itemAccessor(row).abilities,
            });
        }
        useColumns.set('hp', {
            Header: 'HP',
            accessor: (row) => itemAccessor(row).hp.baseValue,
        });
        useColumns.set('attack', {
            Header: 'Atk',
            accessor: (row) => itemAccessor(row).attack.baseValue,
        });
        useColumns.set('defense', {
            Header: 'Def',
            accessor: (row) => itemAccessor(row).defense.baseValue,
        });
        if (hasSpecialStat) {
            useColumns.set('special', {
                Header: 'Spc',
                accessor: (row) => itemAccessor(row).special.baseValue,
            });
        } else {
            useColumns.set('specialAttack', {
                Header: 'SpA',
                accessor: (row) => itemAccessor(row).specialAttack.baseValue,
            });
            useColumns.set('specialDefense', {
                Header: 'SpD',
                accessor: (row) => itemAccessor(row).specialDefense.baseValue,
            });
        }
        useColumns.set('speed', {
            Header: 'Spd',
            accessor: (row) => itemAccessor(row).speed.baseValue,
        });
        useColumns.set('statTotal', {
            Header: 'Tot',
            accessor: (row) => itemAccessor(row).statTotal,
        });
        if (columnsCallback) {
            return columnsCallback(useColumns);
        }
        return useColumns;
    }, [columnsCallback, itemAccessor, hasAbilities, hasSpecialStat]);

    const tableOptions: Partial<DataTableOptions<PokemonTableRecord>> = {
        pageCount: state.pageCount ?? -1,
        manualPagination: true,
        manualSortBy: true,
        initialState: {
            pageIndex: 0,
            pageSize: 10,
        },
    };

    const fetchData = useCallback((newPageIndex: number, newPageSize: number, sortBy: SortBy) => {
        let query = customQuery;
        if (!query) {
            query = (queryPageIndex, queryPageSize, querySortBy) => {
                const params = Object.assign({}, {
                    'species.versionGroup': currentVersion.versionGroup,
                    page: queryPageIndex + 1,
                    itemsPerPage: queryPageSize,
                }, buildOrderParams(querySortBy, sortFieldMap));
                return pktQuery<ApiRecord.HydraCollection<PokemonTableRecord>>('pokemon', params, currentVersion);
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
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading Pokemon.'}]);
        });
        setState({loading: true});
    }, [currentVersion, setFlashes, customQuery]);

    const columnProps: ColumnPropsMap<PokemonTableRecord> = useMemo(() => (Object.assign({}, {
        name: {className: 'text-nowrap'},
    } as ColumnPropsMap<PokemonTableRecord>, customColumnProps ?? {})), [customColumnProps]);

    const cellProps = useMemo(() => (Object.assign({}, customCellProps ?? {})), [customCellProps]);

    const cellRender: CellRenderMap<PokemonTableRecord> = useMemo(() => (Object.assign({}, {
        name: cell => <PokemonLabel pokemon={itemAccessor(cell.row.original)}/>,
        types: cell => <PokemonTypeList types={cell.value}/>,
        abilities: cell => <PokemonAbilityList abilities={cell.value}/>,
    } as CellRenderMap<PokemonTableRecord>, customCellRender ?? {})), [customCellRender, itemAccessor]);

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

function PokemonAbilityList(props: { abilities: Array<ApiRecord.Pokemon.PokemonAbility> }) {
    const {abilities} = props;
    abilities.sort((a, b) => a.position - b.position);
    return (
        <ul className="list-unstyled">
            {abilities.map(ability => (
                <li key={ability.ability['@id']}>
                    <PokemonAbilityLabel ability={ability}/>
                </li>
            ))}
        </ul>
    );
}
