import '../assets/styles/PokemonEvolutionTree.scss';
import 'react-orgchart/index.css';
import AppContext, {AppContextProps} from '../common/Context';
import InfoList from '../common/components/InfoList';
import Loading from '../common/components/Loading';
import OrgChart from 'react-orgchart';
import PktMarkdown from '../common/components/PktMarkdown';
import PokemonLabel from './PokemonLabel';
import React, {useCallback, useContext, useReducer} from 'react';
import resolveElementClasses from '../common/resolveElementClasses';
import {AxiosError} from 'axios';
import {Card} from 'react-bootstrap';
import {FlashSeverity} from '../common/components/Flashes';
import {pktQuery} from '../common/client';

interface PokemonEvolutionTreeState {
    loadedForPokemon?: number
    evolutionTree?: ApiRecord.Pokemon.PokemonEvolutionTree
    loadingEvolutionTree: boolean
}

export default function PokemonEvolutionTree(props: { pokemon: ApiRecord.Pokemon.Pokemon }) {
    // Setup
    const {pokemon} = props;
    const {setFlashes, currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: PokemonEvolutionTreeState, newState: Partial<PokemonEvolutionTreeState>) => ({...state, ...newState}), {
        loadingEvolutionTree: false,
    } as PokemonEvolutionTreeState);
    const {evolutionTree} = state;

    // Reset
    if (evolutionTree && state.loadedForPokemon !== undefined && state.loadedForPokemon !== pokemon.id) {
        setState({evolutionTree: undefined, loadedForPokemon: undefined});
    }

    // Load
    if (!state.loadingEvolutionTree && evolutionTree === undefined) {
        pktQuery<ApiRecord.Pokemon.PokemonEvolutionTree>(`pokemon_evolution_trees/${pokemon.id}`, {}, currentVersion)
            .then((response) => {
                setState({
                    evolutionTree: response.data,
                    loadingEvolutionTree: false,
                    loadedForPokemon: pokemon.id,
                });
            }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading Pokemon evolution.'}]);
        });
        setState({loadingEvolutionTree: true});
    }

    const RenderNode = useCallback((props: { node: ApiRecord.Pokemon.PokemonEvolutionTree }) => (
        <EvolutionLabel active={props.node.pokemon.id === pokemon.id}
                        pokemon={props.node.pokemon}
                        conditions={props.node.conditions}
        />
    ), [pokemon]);

    return (
        <>
            {state.loadingEvolutionTree && <Loading/>}
            {evolutionTree && (
                <div className="pkt-pokemon-evolution">
                    <OrgChart tree={evolutionTree} NodeComponent={RenderNode}/>
                </div>
            )}
        </>
    );
}

interface EvolutionLabelProps extends Pick<ApiRecord.Pokemon.PokemonEvolutionTree, 'pokemon' | 'conditions'> {
    active: boolean
}

function EvolutionLabel(props: EvolutionLabelProps) {
    const {pokemon, conditions: conditionsMap, active} = props;
    const classes = resolveElementClasses(active ? ['active'] : undefined, 'pkt-pokemon-evolution-card');

    return (
        <Card className={classes}>
            <Card.Body>
                <Card.Title>
                    <PokemonLabel pokemon={pokemon}/>
                    {active && <span className="sr-only">(This Pokémon)</span>}
                </Card.Title>
                {Object.keys(conditionsMap).length > 0 && (
                    <InfoList>
                        {Object.entries(conditionsMap).map(([trigger, conditions], triggerIndex) => (
                            <InfoList.Item key={`evo-${pokemon.id}-condition-${triggerIndex}`} name={trigger}>
                                <ul className="list-unstyled">
                                    {conditions.map((condition, conditionIndex) => (
                                        <li key={`evo-${pokemon.id}-condition-${triggerIndex}-${conditionIndex}`}
                                            className="pkt-pokemon-evolution-condition">
                                            <PktMarkdown>{condition}</PktMarkdown>
                                        </li>
                                    ))}
                                </ul>
                            </InfoList.Item>
                        ))}
                    </InfoList>
                )}
            </Card.Body>
        </Card>
    );
}
