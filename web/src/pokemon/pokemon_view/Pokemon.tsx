import PokemonTypeList from '../PokemonTypeList';
import PktMarkdown from '../../common/components/PktMarkdown';
import React from 'react';
import PokemonFlavorText from './PokemonFlavorText';
import PokemonFormsList from './PokemonFormsList';
import './Pokemon.scss';

interface PokemonProps {
    species: ApiRecord.Pokemon.PokemonSpeciesInVersionGroup
    pokemon: ApiRecord.Pokemon.Pokemon.PokemonView
}

export default function Pokemon(props: PokemonProps) {
    const {species, pokemon} = props;

    return (
        <div>
            {/* Types */}
            <PokemonTypeList types={pokemon.types}/>

            {/* Flavor text */}
            <p>
                {pokemon.genus && (<span className="pkt-pokemon-view-genus">{pokemon.genus}</span>)}
                {pokemon.flavorText && (<PokemonFlavorText pokemon={pokemon}/>)}
            </p>

            {pokemon.formsNote && (<PktMarkdown>{pokemon.formsNote}</PktMarkdown>)}

            <PokemonFormsList species={species} pokemon={pokemon}/>
        </div>
    );
}
