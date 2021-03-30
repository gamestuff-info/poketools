import EntityLabel from '../common/components/EntityLabel';
import {Image} from 'react-bootstrap';
import {AssetPackage, getAssetUrl} from '../common/getAssetUrl';
import React from 'react';

export default function PokemonHabitatLabel(props: { habitat: ApiRecord.Pokemon.PokemonHabitat }) {
    const {habitat} = props;
    return (
        <EntityLabel>
            <Image src={getAssetUrl(`habitat/${habitat.icon}`, AssetPackage.MEDIA)} thumbnail/>
            <EntityLabel.Text>{habitat.name}</EntityLabel.Text>
        </EntityLabel>
    );
}
