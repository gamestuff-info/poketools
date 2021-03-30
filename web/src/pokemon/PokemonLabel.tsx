import EntityLabel from '../common/components/EntityLabel';
import {AssetPackage, getAssetUrl} from '../common/getAssetUrl';
import React, {useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {RouteParams, Routes} from '../routes';
import {generatePath, Link} from 'react-router-dom';

interface PokemonLabelPropsBase {
    noLink?: boolean
}

export type PokemonLabelSpecies = Pick<ApiRecord.Pokemon.PokemonSpeciesInVersionGroup, 'defaultPokemon' | 'name' | 'slug'>;

interface PokemonLabelPropsSpecies extends PokemonLabelPropsBase {
    species: PokemonLabelSpecies
}

export type PokemonLabelPokemon = Pick<ApiRecord.Pokemon.Pokemon, 'defaultForm' | 'name' | 'slug' | 'speciesSlug'>;

interface PokemonLabelPropsPokemon extends PokemonLabelPropsBase {
    pokemon: PokemonLabelPokemon
}

export type PokemonLabelForm = Pick<ApiRecord.Pokemon.PokemonForm, 'icon' | 'name' | 'slug' | 'pokemonSlug' | 'speciesSlug'>;

interface PokemonLabelPropsForm extends PokemonLabelPropsBase {
    form: PokemonLabelForm
}

type PokemonLabelProps = PokemonLabelPropsSpecies | PokemonLabelPropsPokemon | PokemonLabelPropsForm;

export default function PokemonLabel(props: PokemonLabelProps) {
    const noLink = props.noLink ?? false;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    let icon = null;
    let name;
    let pathParams: RouteParams.Pokemon.View;
    if ('species' in props) {
        const {species} = props;
        icon = species.defaultPokemon.defaultForm.icon;
        name = species.name;
        pathParams = {
            version: currentVersion.slug,
            species: species.slug,
        };
    } else if ('pokemon' in props) {
        const {pokemon} = props;
        icon = pokemon.defaultForm.icon;
        name = pokemon.name;
        pathParams = {
            version: currentVersion.slug,
            species: pokemon.speciesSlug,
            pokemon: pokemon.slug,
        };
    } else if ('form' in props) {
        const {form} = props;
        icon = form.icon;
        name = form.name;
        pathParams = {
            version: currentVersion.slug,
            species: form.speciesSlug,
            pokemon: form.pokemonSlug,
            form: form.slug,
        };
    } else {
        throw Error('Unhandled Pokemon label type');
    }

    const label = (
        <EntityLabel>
            {icon && <EntityLabel.Icon src={getAssetUrl(`/pokemon/icon/${icon}`, AssetPackage.MEDIA)}/>}
            <EntityLabel.Text>{name}</EntityLabel.Text>
        </EntityLabel>
    );
    if (!noLink) {
        return (
            <Link to={generatePath(Routes.POKEMON_VIEW, pathParams)}>
                {label}
            </Link>
        );
    }
    return label;
}
