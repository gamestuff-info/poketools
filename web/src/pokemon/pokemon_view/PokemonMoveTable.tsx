import {useCallback, useContext, useMemo, useReducer} from 'react';
import AppContext, {AppContextProps} from '../../common/Context';
import {pktQuery} from '../../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../../common/components/Flashes';
import Loading from '../../common/components/Loading';
import {Tab, Tabs} from 'react-bootstrap';
import PktMarkdown from '../../common/components/PktMarkdown';
import {
    buildOrderParams,
    CellPropsMap,
    CellRenderMap,
    ColumnPropsMap,
    DataTableColumnOptions,
} from '../../common/components/DataTable';
import ItemLabel from '../../item/ItemLabel';
import MoveTable, {MoveTableRecord} from '../../move/MoveTable';

interface PokemonMoveTableTableState {
    forPokemon?: number
    learnMethods?: Array<ApiRecord.Move.MoveLearnMethod> | null
    loadingLearnMethods: boolean
}

export default function PokemonMoveTable(props: { pokemon: ApiRecord.Pokemon.Pokemon }) {
    const {pokemon} = props;
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: PokemonMoveTableTableState, newState: Partial<PokemonMoveTableTableState>) => ({...state, ...newState}), {
        loadingLearnMethods: false,
    } as PokemonMoveTableTableState);
    const {learnMethods} = state;

    // Load applicable learn methods
    if (!state.loadingLearnMethods && (learnMethods === undefined || (state.forPokemon !== undefined && state.forPokemon !== pokemon.id))) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Move.MoveLearnMethod>>('move_learn_methods', {
            pokemon: pokemon.id,
            pagination: false,
        }, currentVersion).then(response => {
            setState({
                forPokemon: pokemon.id,
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
                    <LearnMethodMoveTable pokemon={pokemon} method={learnMethod}/>
                </Tab>
            ))}
        </Tabs>
    );
}

const sortFieldMap = {
    method: 'learnMethod.position',
    type: 'move.type.position',
    contestType: 'move.contestType.position',
    damageClass: 'move.damageClass.position',
};

function LearnMethodMoveTable(props: { pokemon: ApiRecord.Pokemon.Pokemon, method: ApiRecord.Move.MoveLearnMethod }) {
    const {pokemon, method} = props;
    const {id: pokemonId} = pokemon;
    const {id: methodId, slug: methodSlug} = method;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    const query = useCallback((queryPageIndex, queryPageSize, querySortBy) => {
        const params = Object.assign({}, {
            pokemon: pokemonId,
            learnMethod: methodId,
            page: queryPageIndex + 1,
            itemsPerPage: queryPageSize,
            groups: ['pokemon_view'],
        }, buildOrderParams(querySortBy, sortFieldMap));
        return pktQuery<ApiRecord.HydraCollection<MoveTableRecord>>('pokemon_moves', params, currentVersion);
    }, [pokemonId, methodId, currentVersion]);

    const columnsCallback = useCallback((columns: Map<string, DataTableColumnOptions<MoveTableRecord>>) => {
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

    const itemAccessor = useCallback((row: ApiRecord.Pokemon.PokemonMove.PokemonView) => row.move, []);

    const columnProps: ColumnPropsMap<ApiRecord.Pokemon.PokemonMove.PokemonView> = useMemo(() => ({
        machine: {className: 'pkt-text'},
    }), []);

    const physicalStat = useMemo(() => pokemon.attack.baseValue, [pokemon]);
    const specialStat = useMemo(() => {
        if (currentVersion.featureSlugs.includes('special-stat') && pokemon.special !== undefined) {
            return pokemon.special.baseValue;
        } else if (pokemon.specialAttack !== undefined) {
            return pokemon.specialAttack.baseValue;
        }
        return 0;
    }, [currentVersion, pokemon]);
    const cellProps: CellPropsMap<ApiRecord.Pokemon.PokemonMove.PokemonView> = useMemo(() => ({
        type: cell => {
            if (pokemon.types.some(pokemonType => pokemonType.type.id === cell.row.original.move.type.id)) {
                return {className: 'pkt-pokemon-view-moves-stab'};
            }
            return {};
        },
        damageClass: cell => {
            const forPhysical = physicalStat > specialStat && cell.row.original.move.effectiveDamageClass.slug === 'physical';
            const forSpecial = physicalStat < specialStat && cell.row.original.move.effectiveDamageClass.slug === 'special';
            if (forPhysical || forSpecial) {
                return {className: 'pkt-pokemon-view-moves-stab'};
            }

            return {};
        },
    }), [physicalStat, specialStat, pokemon]);

    const cellRender: CellRenderMap<ApiRecord.Pokemon.PokemonMove.PokemonView> = useMemo(() => ({
        machine: cell => <ItemLabel item={cell.value}/>,
    }), []);

    return (
        <MoveTable query={query}
                   itemAccessor={itemAccessor}
                   columnsCallback={columnsCallback}
                   columnProps={columnProps}
                   cellProps={cellProps}
                   cellRender={cellRender}
        />
    );
}
