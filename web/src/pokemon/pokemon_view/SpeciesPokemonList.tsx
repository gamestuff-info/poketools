import {generatePath, Link, useParams} from 'react-router-dom';
import {RouteParams, Routes} from '../../routes';
import React, {useContext, useReducer} from 'react';
import AppContext, {AppContextProps} from '../../common/Context';
import {pktQuery} from '../../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../../common/components/Flashes';
import Loading from '../../common/components/Loading';
import {Nav, Tab} from 'react-bootstrap';
import PokemonLabel from '../PokemonLabel';
import Pokemon from './Pokemon';

interface SpeciesPokemonListState {
    loadedForSpecies?: number
    speciesPokemon?: Array<ApiRecord.Pokemon.Pokemon.PokemonView>
    loadingPokemon: boolean
}

export default function SpeciesPokemonList(props: { species: ApiRecord.Pokemon.PokemonSpeciesInVersionGroup }) {
    // Setup
    const {species} = props;
    const {pokemon: pokemonSlug} = useParams<RouteParams.Pokemon.View>();
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: SpeciesPokemonListState, newState: Partial<SpeciesPokemonListState>) => ({...state, ...newState}), {
        loadingPokemon: false,
    } as SpeciesPokemonListState);
    const {speciesPokemon} = state;

    // Reset
    if (speciesPokemon && state.loadedForSpecies !== undefined && state.loadedForSpecies !== species.id) {
        setState({speciesPokemon: undefined, loadedForSpecies: undefined});
    }

    // Load
    if (!state.loadingPokemon && speciesPokemon === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Pokemon.Pokemon.PokemonView>>('pokemon', {
            versionGroup: currentVersion.versionGroup,
            species: species.id,
            page: 1,
            itemsPerPage: 100,
            groups: ['pokemon_view'],
        }, currentVersion).then((response) => {
            setState({
                speciesPokemon: response.data['hydra:member'],
                loadingPokemon: false,
                loadedForSpecies: species.id,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading Pokemon.'}]);
        });
        setState({loadingPokemon: true});
    }

    return (
        <div>
            {state.loadingPokemon && <Loading/>}
            {speciesPokemon && speciesPokemon.length > 1 && (
                <Tab.Container defaultActiveKey={`pokemon-${pokemonSlug ?? species.defaultPokemon.slug}`}>
                    <Nav variant="tabs">
                        {speciesPokemon.map(pokemon => (
                            <Nav.Item key={`pokemon-${pokemon.slug}`}>
                                <Nav.Link
                                    eventKey={`pokemon-${pokemon.slug}`}
                                    as={Link}
                                    to={generatePath(Routes.POKEMON_VIEW, {
                                        version: currentVersion.slug,
                                        species: species.slug,
                                        pokemon: pokemon.slug,
                                    })}>
                                    <PokemonLabel pokemon={pokemon} noLink/>
                                </Nav.Link>
                            </Nav.Item>
                        ))}
                    </Nav>
                    <Tab.Content>
                        {speciesPokemon.map(pokemon => (
                            <Tab.Pane key={`pokemon-${pokemon.slug}`}
                                      eventKey={`pokemon-${pokemon.slug}`}
                            >
                                <Pokemon species={species} pokemon={pokemon}/>
                            </Tab.Pane>
                        ))}
                    </Tab.Content>
                </Tab.Container>
            )}
            {speciesPokemon && speciesPokemon.length === 1 && (
                <Pokemon species={species} pokemon={speciesPokemon[0]}/>
            )}
        </div>
    );
}
