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
import PokemonForm from './PokemonForm';

interface PokemonFormListProps {
    species: ApiRecord.Pokemon.PokemonSpeciesInVersionGroup
    pokemon: ApiRecord.Pokemon.Pokemon.PokemonView
}

interface PokemonFormListState {
    loadedForPokemon?: number
    pokemonForms?: Array<ApiRecord.Pokemon.PokemonForm.PokemonView>
    loadingForms: boolean
}

export default function PokemonFormsList(props: PokemonFormListProps) {
    // Setup
    const {species, pokemon} = props;
    const {form: formSlug} = useParams<RouteParams.Pokemon.View>();
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: PokemonFormListState, newState: Partial<PokemonFormListState>) => ({...state, ...newState}), {
        loadingForms: false,
    } as PokemonFormListState);
    const {pokemonForms} = state;

    // Reset
    if (pokemonForms && state.loadedForPokemon !== undefined && state.loadedForPokemon !== pokemon.id) {
        setState({pokemonForms: undefined, loadedForPokemon: undefined});
    }

    // Load
    if (!state.loadingForms && pokemonForms === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Pokemon.PokemonForm.PokemonView>>('pokemon_forms', {
            pokemon: pokemon.id,
            page: 1,
            itemsPerPage: 100,
            groups: ['pokemon_view'],
        }, currentVersion).then((response) => {
            setState({
                pokemonForms: response.data['hydra:member'],
                loadingForms: false,
                loadedForPokemon: pokemon.id,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading Pokemon.'}]);
        });
        setState({loadingForms: true});
    }

    return (
        <div>
            {state.loadingForms && <Loading/>}
            {pokemonForms && pokemonForms.length > 1 && (
                <Tab.Container defaultActiveKey={`form-${formSlug ?? pokemon.defaultForm.slug}`}>
                    <Nav variant="tabs">
                        {pokemonForms.map(form => (
                            <Nav.Item key={`form-${form.slug}`}>
                                <Nav.Link
                                    eventKey={`form-${form.slug}`}
                                    as={Link}
                                    to={generatePath(Routes.POKEMON_VIEW, {
                                        version: currentVersion.slug,
                                        species: species.slug,
                                        pokemon: pokemon.slug,
                                        form: form.slug,
                                    })}>
                                    <PokemonLabel form={form} noLink/>
                                </Nav.Link>
                            </Nav.Item>
                        ))}
                    </Nav>
                    <Tab.Content>
                        {pokemonForms.map(form => (
                            <Tab.Pane key={`form-${form.slug}`}
                                      eventKey={`form-${form.slug}`}
                            >
                                <PokemonForm species={species} pokemon={pokemon} form={form}/>
                            </Tab.Pane>
                        ))}
                    </Tab.Content>
                </Tab.Container>
            )}
            {pokemonForms && pokemonForms.length === 1 && (
                <PokemonForm species={species} pokemon={pokemon} form={pokemonForms[0]}/>
            )}
        </div>
    );
}
