import React, {useMemo, useState} from 'react';
import InfoList from '../../common/components/InfoList';
import LinearGauge from '../../common/components/gauge/LinearGauge';
import {Button, Modal} from 'react-bootstrap';
import PokemonBreedingTable from '../PokemonBreedingTable';
import PokemonHatchSteps from './PokemonHatchSteps';
import './PokemonBreeding.scss';

export default function PokemonBreeding(props: { pokemon: ApiRecord.Pokemon.Pokemon.PokemonView }) {
    const {pokemon} = props;
    const [eggGroupCompatibilityShown, setEggGroupCompatibilityShown] = useState(false);
    const eggGroupNames = useMemo(() => pokemon.eggGroups.map(eggGroup => eggGroup.name).join(', '), [pokemon]);
    const canBreed = useMemo(() => !pokemon.baby && !pokemon.eggGroups.some(eggGroup => eggGroup.slug === 'undiscovered'), [pokemon]);

    return (
        <InfoList>
            <InfoList.Item name="Gender" className="pkt-pokemon-view-gender">
                {pokemon.femaleRate === null && <span>Genderless</span>}
                {pokemon.femaleRate !== null && (
                    <>
                        <LinearGauge value={[100 - pokemon.femaleRate, pokemon.femaleRate]}/>
                        <span className="text-nowrap">
                            {100 - pokemon.femaleRate}% Male; {pokemon.femaleRate}% Female
                        </span>
                    </>
                )}
            </InfoList.Item>
            <InfoList.Item name="Egg Groups">
                {eggGroupNames}
                {!canBreed && ' (Cannot breed)'}
                {canBreed && (
                    <>
                        <Button className="ml-1" variant="info" size="sm"
                                onClick={() => setEggGroupCompatibilityShown(true)}>
                            Show Compatibility
                        </Button>
                        <Modal size="xl"
                               show={eggGroupCompatibilityShown}
                               onHide={() => setEggGroupCompatibilityShown(false)}>
                            <Modal.Header closeButton>
                                <Modal.Title>
                                    {eggGroupNames}
                                </Modal.Title>
                            </Modal.Header>
                            <Modal.Body>
                                <PokemonBreedingTable pokemon={pokemon}/>
                            </Modal.Body>
                        </Modal>
                    </>
                )}
            </InfoList.Item>
            {canBreed && pokemon.hatchSteps && (
                <InfoList.Item name="Hatch Steps">
                    <PokemonHatchSteps pokemon={pokemon}/>
                </InfoList.Item>
            )}
        </InfoList>
    );
}
