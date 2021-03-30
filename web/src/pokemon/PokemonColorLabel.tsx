import EntityLabel from '../common/components/EntityLabel';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {faCircle} from '@fortawesome/free-solid-svg-icons';
import React from 'react';

export default function PokemonColorLabel(props: { color: ApiRecord.Pokemon.PokemonColor }) {
    const {color: pokemonColor} = props;
    return (
        <EntityLabel>
            <FontAwesomeIcon icon={faCircle} style={{color: pokemonColor.cssColor}}/>
            <EntityLabel.Text className="pl-1">{pokemonColor.name}</EntityLabel.Text>
        </EntityLabel>
    );
}
