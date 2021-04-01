import './PokemonView.scss';
import AppContext, {AppContextProps} from '../common/Context';
import Loading from '../common/components/Loading';
import NotFound from '../common/components/NotFound';
import PokemonLabel from './PokemonLabel';
import React, {useContext, useReducer} from 'react';
import setPageTitle from '../common/setPageTitle';
import useVersionRedirect from '../common/components/useVersionRedirect';
import {AxiosError} from 'axios';
import {Breadcrumb} from 'react-bootstrap';
import {FlashSeverity} from '../common/components/Flashes';
import {RouteParams, Routes} from '../routes';
import {generatePath, Link, useParams} from 'react-router-dom';
import {pktQuery} from '../common/client';
import SpeciesPokemonList from './pokemon_view/SpeciesPokemonList';

interface PokemonViewState {
    species?: ApiRecord.Pokemon.PokemonSpeciesInVersionGroup | null
    loadingSpecies: boolean
}

export default function PokemonView(props: {}) {
    // Setup
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const {species: speciesSlug} = useParams<RouteParams.Pokemon.View>();
    const [state, setState] = useReducer((state: PokemonViewState, newState: Partial<PokemonViewState>) => ({...state, ...newState}), {
        loadingSpecies: false,
    } as PokemonViewState);
    const {species} = state;

    // Version redirect
    let redirect;
    if ((redirect = useVersionRedirect(currentVersion))) {
        return redirect;
    }

    // Reset
    if (species && (speciesSlug !== species.slug || currentVersion.versionGroup !== species.versionGroup)) {
        setState({species: undefined});
    }

    // Load
    if (species === null) {
        return (<NotFound/>);
    } else if (!state.loadingSpecies && species === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Pokemon.PokemonSpeciesInVersionGroup>>('pokemon_species_in_version_groups', {
            versionGroup: currentVersion.versionGroup,
            slug: speciesSlug,
            page: 1,
            itemsPerPage: 1,
            groups: ['pokemon_view'],
        }, currentVersion).then((response) => {
            if (response.data['hydra:member'].length === 0) {
                setState({species: null, loadingSpecies: false});
            } else {
                setState({species: response.data['hydra:member'][0], loadingSpecies: false});
            }
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading Pokemon.'}]);
        });
        setState({loadingSpecies: true});
    } else if (species) {
        setPageTitle(['Pokémon', species.name]);
    }

    return (
        <div>
            <Breadcrumb>
                <Breadcrumb.Item linkAs="span">{currentVersion.name}</Breadcrumb.Item>
                <Breadcrumb.Item linkAs={Link}
                                 linkProps={{to: generatePath(Routes.POKEMON_INDEX, {version: currentVersion.slug})}}>
                    Pokémon
                </Breadcrumb.Item>
                <Breadcrumb.Item active>
                    {!species && <Loading uncontained/>}
                    {species && species.name}
                </Breadcrumb.Item>
            </Breadcrumb>

            {state.loadingSpecies && <Loading/>}
            {species && (
                <div>
                    <h1><PokemonLabel species={species} noLink/></h1>
                    <SpeciesNumbers species={species}/>

                    <SpeciesPokemonList species={species}/>
                </div>
            )}
        </div>
    );
}

function SpeciesNumbers(props: { species: ApiRecord.Pokemon.PokemonSpeciesInVersionGroup }) {
    const {species} = props;
    return (
        <ul className="pkt-pokemon-view-numbers">
            {species.numbers.map(speciesNumber => (
                <li key={speciesNumber.pokedex['@id']}>
                    <span className="pkt-pokemon-view-numbers-dex">{speciesNumber.pokedex.name}</span>
                    &nbsp;
                    <span className="pkt-pokemon-view-numbers-number">{speciesNumber.number}</span>
                </li>
            ))}
        </ul>
    );
}
