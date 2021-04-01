import {useCallback, useContext, useMemo, useReducer} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../common/components/Flashes';
import Loading from '../common/components/Loading';
import {Tab, Tabs} from 'react-bootstrap';
import PokemonTable, {PokemonTableRecord} from '../pokemon/PokemonTable';
import {buildOrderParams, CellRenderMap, ColumnPropsMap, DataTableColumnOptions} from '../common/components/DataTable';
import RadialGauge from '../common/components/gauge/RadialGauge';
import './LocationPokemonTable.scss';
import EncounterConditionList from '../pokemon/EncounterConditionList';

interface LocationPokemonTableState {
    forArea?: number
    encounterMethods?: Array<ApiRecord.Pokemon.EncounterMethod> | null
    loadingEncounterMethods: boolean
}

export default function LocationPokemonTable(props: { area: ApiRecord.Location.LocationArea }) {
    const {area} = props;
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: LocationPokemonTableState, newState: Partial<LocationPokemonTableState>) => ({...state, ...newState}), {
        loadingEncounterMethods: false,
    } as LocationPokemonTableState);
    const {encounterMethods} = state;

    // Load applicable learn methods
    if (!state.loadingEncounterMethods && (encounterMethods === undefined || (state.forArea !== undefined && state.forArea !== area.id))) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Pokemon.EncounterMethod>>('encounter_methods', {
            locationArea: area.id,
            pagination: false,
        }, currentVersion).then(response => {
            setState({
                forArea: area.id,
                encounterMethods: response.data['hydra:member'],
                loadingEncounterMethods: false,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading learn methods.'}]);
        });
        setState({loadingEncounterMethods: true});
    }

    if (!encounterMethods) {
        return (<Loading/>);
    } else if (encounterMethods.length === 0) {
        return (
            <p>
                No wild Pokémon are found here.
            </p>
        );
    }

    return (
        <Tabs defaultActiveKey={encounterMethods[0]['@id']}>
            {encounterMethods.map(encounterMethod => (
                <Tab key={encounterMethod['@id']} eventKey={encounterMethod['@id']} title={encounterMethod.name}>
                    <EncounterMethodPokemonTable area={area} method={encounterMethod}/>
                </Tab>
            ))}
        </Tabs>
    );
}

const sortFieldMap = {
    level: 'level.min',
    name: 'pokemon.name',
    types: 'pokemon.types.type.position',
    abilities: 'pokemon.abilities.ability.name',
    hp: 'pokemon.hp.baseValue',
    attack: 'pokemon.attack.baseValue',
    defense: 'pokemon.defense.baseValue',
    specialAttack: 'pokemon.specialAttack.baseValue',
    specialDefense: 'pokemon.specialDefense.baseValue',
    special: 'pokemon.special.baseValue',
    speed: 'pokemon.speed.baseValue',
    statTotal: 'pokemon.statTotal',
};

function EncounterMethodPokemonTable(props: { area: ApiRecord.Location.LocationArea, method: ApiRecord.Pokemon.EncounterMethod }) {
    const {area, method} = props;
    const {id: areaId} = area;
    const {id: methodId} = method;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    const query = useCallback((queryPageIndex, queryPageSize, querySortBy) => {
        const params = Object.assign({}, {
            version: currentVersion.id,
            locationArea: areaId,
            method: methodId,
            page: queryPageIndex + 1,
            itemsPerPage: queryPageSize,
            groups: ['location_view'],
        }, buildOrderParams(querySortBy, sortFieldMap));
        return pktQuery<ApiRecord.HydraCollection<PokemonTableRecord>>('encounters', params, currentVersion);
    }, [areaId, methodId, currentVersion]);

    const columnsCallback = useCallback((columns: Map<string, DataTableColumnOptions<PokemonTableRecord>>) => {
        const addOns: Map<string, DataTableColumnOptions<PokemonTableRecord>> = new Map();
        addOns.set('chance', {
            Header: 'Chance',
            accessor: (row) => row.chance ?? 0,
        });
        addOns.set('level', {
            Header: 'Lv',
            accessor: (row) => row.level ?? 0,
        });
        addOns.set('conditions', {
            Header: 'Cond.',
            accessor: 'conditions',
        });
        return new Map([...addOns, ...columns]);
    }, []);

    const itemAccessor = useCallback((row: ApiRecord.Pokemon.Encounter.LocationView) => row.pokemon, []);

    const columnProps: ColumnPropsMap<ApiRecord.Pokemon.Encounter.LocationView> = useMemo(() => ({
        level: {className: 'text-nowrap'},
    }), []);

    const cellRender: CellRenderMap<ApiRecord.Pokemon.Encounter.LocationView> = useMemo(() => ({
        chance: (cell) => (cell.value > 0 ? (<RadialGauge value={cell.value}/>) : (<>*</>)),
        conditions: (cell) => (<EncounterConditionList encounter={cell.row.original}/>),
    }), []);

    return (
        <PokemonTable query={query}
                      itemAccessor={itemAccessor}
                      columnsCallback={columnsCallback}
                      columnProps={columnProps}
                      cellRender={cellRender}
        />
    );
}
