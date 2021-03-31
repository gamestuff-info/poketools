import {useCallback, useContext, useMemo, useReducer} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {buildOrderParams, CellRenderMap, ColumnPropsMap, DataTableColumnOptions} from '../common/components/DataTable';
import PokemonTable, {PokemonTableRecord} from '../pokemon/PokemonTable';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../common/components/Flashes';
import Loading from '../common/components/Loading';
import {Tab, Tabs} from 'react-bootstrap';
import PktMarkdown from '../common/components/PktMarkdown';
import ItemLabel from '../item/ItemLabel';

interface MovePokemonTableState {
    forMove?: number
    learnMethods?: Array<ApiRecord.Move.MoveLearnMethod> | null
    loadingLearnMethods: boolean
}

/**
 * Pokemon that can learn a move
 */
export default function MovePokemonTable(props: { move: ApiRecord.Move.MoveInVersionGroup }) {
    const {move} = props;
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: MovePokemonTableState, newState: Partial<MovePokemonTableState>) => ({...state, ...newState}), {
        loadingLearnMethods: false,
    } as MovePokemonTableState);
    const {learnMethods} = state;

    // Load applicable learn methods
    if (!state.loadingLearnMethods && (learnMethods === undefined || (state.forMove !== undefined && state.forMove !== move.id))) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Move.MoveLearnMethod>>('move_learn_methods', {
            move: move.id,
            pagination: false,
        }, currentVersion).then(response => {
            setState({
                forMove: move.id,
                learnMethods: response.data['hydra:member'],
                loadingLearnMethods: false,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading learn methods.'}]);
        });
        setState({loadingLearnMethods: true});
    }

    if (!learnMethods) {
        return (<Loading/>);
    }

    return (
        <Tabs defaultActiveKey={learnMethods.length > 0 ? learnMethods[0]['@id'] : undefined}>
            {learnMethods.map(learnMethod => (
                <Tab key={learnMethod['@id']} eventKey={learnMethod['@id']} title={learnMethod.name}>
                    <PktMarkdown>{learnMethod.description}</PktMarkdown>
                    <LearnMethodMovePokemonTable move={move} method={learnMethod}/>
                </Tab>
            ))}
        </Tabs>
    );
}

const sortFieldMap = {
    method: 'learnMethod.position',
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

/**
 * Pokemon that can learn a move via a method
 */
export function LearnMethodMovePokemonTable(props: { move: ApiRecord.Move.MoveInVersionGroup, method: ApiRecord.Move.MoveLearnMethod }) {
    const {move, method} = props;
    const {id: moveId} = move;
    const {id: methodId, slug: methodSlug} = method;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    const query = useCallback((queryPageIndex, queryPageSize, querySortBy) => {
        const params = Object.assign({}, {
            move: moveId,
            learnMethod: methodId,
            page: queryPageIndex + 1,
            itemsPerPage: queryPageSize,
            groups: ['move_view'],
        }, buildOrderParams(querySortBy, sortFieldMap));
        return pktQuery<ApiRecord.HydraCollection<PokemonTableRecord>>('pokemon_moves', params, currentVersion);
    }, [moveId, methodId, currentVersion]);

    const columnsCallback = useCallback((columns: Map<string, DataTableColumnOptions<PokemonTableRecord>>) => {
        const addOns = new Map();
        if (methodSlug === 'level-up') {
            addOns.set('level', {
                Header: 'Lv',
                accessor: 'level',
            });
        } else if (methodSlug === 'machine') {
            addOns.set('machine', {
                Header: 'Machine',
                accessor: 'machine',
            });
        }
        return new Map([...addOns, ...columns]);
    }, [methodSlug]);

    const itemAccessor = useCallback((row: ApiRecord.Pokemon.PokemonMove.MoveView) => row.pokemon, []);

    const columnProps: ColumnPropsMap<ApiRecord.Pokemon.PokemonMove.PokemonView> = useMemo(() => ({
        machine: {className: 'pkt-text'},
    }), []);

    const cellRender: CellRenderMap<ApiRecord.Pokemon.PokemonMove.PokemonView> = useMemo(() => ({
        machine: cell => <ItemLabel item={cell.value}/>,
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
