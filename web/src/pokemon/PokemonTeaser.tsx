import {useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {AssetPackage, getAssetUrl} from '../common/getAssetUrl';
import Teaser from '../search/Teaser';
import {generatePath} from 'react-router-dom';
import {Routes} from '../routes';

export default function PokemonTeaser(props: { pokemon: ApiRecord.Pokemon.Pokemon }) {
    const {pokemon} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const image = pokemon.defaultForm.icon ? (
        <img src={getAssetUrl(`/pokemon/icon/${pokemon.defaultForm.icon}`, AssetPackage.MEDIA)} alt=""/>) : undefined;

    return (
        <Teaser label={pokemon.name}
                image={image}
                href={generatePath(Routes.POKEMON_VIEW, {
                    version: currentVersion.slug,
                    species: pokemon.speciesSlug,
                    pokemon: pokemon.slug,
                })}
        />
    );
}
