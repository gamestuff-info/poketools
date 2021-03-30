import React, {useContext, useMemo} from 'react';
import AppContext, {AppContextProps} from '../../common/Context';
import {Col, Row} from 'react-bootstrap';
import PokemonEvolutionTree from '../PokemonEvolutionTree';
import PokemonLocationTable from '../PokemonLocationTable';
import PokemonFormMedia from './PokemonFormMedia';
import PokemonStats from './PokemonStats';
import PokemonTraining from './PokemonTraining';
import TypeChart from '../../type/TypeChart';
import InfoList from '../../common/components/InfoList';
import PokemonAbilityLabel from '../PokemonAbilityLabel';
import PktMarkdown from '../../common/components/PktMarkdown';
import PokemonBreeding from './PokemonBreeding';
import PokemonWildHeldItemsTable from './PokemonWildHeldItemsTable';
import PokemonFormPokeathlonStats from './PokemonFormPokeathlon';
import RadialGauge from '../../common/components/gauge/RadialGauge';
import PokemonMoveTable from './PokemonMoveTable';

export interface PokemonFormProps {
    species: ApiRecord.Pokemon.PokemonSpeciesInVersionGroup
    pokemon: ApiRecord.Pokemon.Pokemon.PokemonView
    form: ApiRecord.Pokemon.PokemonForm.PokemonView
}

export default function PokemonForm(props: PokemonFormProps) {
    const {pokemon, form} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const hasAbilities = useMemo(() => currentVersion.featureSlugs.includes('abilities'), [currentVersion]);
    const hasBreeding = useMemo(() => currentVersion.featureSlugs.includes('breeding'), [currentVersion]);
    const hasPokeathlon = useMemo(() => currentVersion.featureSlugs.includes('pokeathlon'), [currentVersion]);
    const hasPalPark = useMemo(() => currentVersion.featureSlugs.includes('pal-park'), [currentVersion]);

    return (
        <div>
            <Row>
                <Col>
                    <PokemonFormMedia pokemon={pokemon} form={form}/>
                </Col>
                <Col>
                    <h2>Stats</h2>
                    <PokemonStats pokemon={pokemon}/>
                </Col>
                <Col>
                    <h2>Training</h2>
                    <PokemonTraining pokemon={pokemon}/>
                </Col>
            </Row>

            <Row>
                <Col>
                    <h2>Damage taken</h2>
                    <PokemonDamageTaken types={pokemon.types}/>
                </Col>
            </Row>

            <Row>
                {hasAbilities && (
                    <Col>
                        <h2>Abilities</h2>
                        <PokemonAbilities pokemon={pokemon}/>
                    </Col>
                )}
                {hasBreeding && (
                    <Col>
                        <h2>Breeding</h2>
                        <PokemonBreeding pokemon={pokemon}/>
                    </Col>
                )}
                {hasPokeathlon && (
                    <Col>
                        <h2>Pokéathlon</h2>
                        <PokemonFormPokeathlonStats pokemonForm={form}/>
                    </Col>
                )}
            </Row>

            <Row>
                <Col>
                    <h2>Wild Held Items</h2>
                    <PokemonWildHeldItemsTable pokemon={pokemon}/>
                </Col>
            </Row>

            <Row>
                <Col>
                    <h2>Evolution</h2>
                    <PokemonEvolutionTree pokemon={pokemon}/>
                </Col>
            </Row>

            <Row>
                <Col>
                    <h2>Locations</h2>
                    {hasPalPark && pokemon.palParkData && (
                        <>
                            <h3>Pal Park</h3>
                            <PokemonPalParkLocation palParkData={pokemon.palParkData}/>

                            <h3>Wild Encounters</h3>
                        </>
                    )}
                    <PokemonLocationTable pokemon={pokemon}/>
                </Col>
            </Row>

            <Row>
                <Col>
                    <h2>Moves</h2>
                    <PokemonMoveTable pokemon={pokemon}/>
                </Col>
            </Row>
        </div>
    );
}

function PokemonDamageTaken(props: { types: Array<ApiRecord.Pokemon.PokemonType> }) {
    const {types} = props;
    const typeIds = useMemo(() => types.map(pokemonType => pokemonType.type.id), [types]) as [number, number?];

    return (
        <div>
            {types.length > 0 && (
                <TypeChart defendingType={typeIds}/>
            )}
        </div>
    );
}

function PokemonAbilities(props: { pokemon: ApiRecord.Pokemon.Pokemon.PokemonView }) {
    const {pokemon} = props;

    return (
        <InfoList>
            {pokemon.abilities.map(pokemonAbility => (
                <InfoList.Item key={pokemonAbility.ability['@id']}
                               name={<PokemonAbilityLabel ability={pokemonAbility}/>}>
                    <PktMarkdown>{pokemonAbility.ability.shortDescription ?? ''}</PktMarkdown>
                </InfoList.Item>
            ))}
        </InfoList>
    );
}

function PokemonPalParkLocation(props: { palParkData: ApiRecord.Pokemon.PokemonPalParkData }) {
    const {palParkData} = props;
    return (
        <InfoList>
            <InfoList.Item name="Area">{palParkData.area.name}</InfoList.Item>
            <InfoList.Item name="Rate"><RadialGauge value={palParkData.rate}/></InfoList.Item>
            <InfoList.Item name="Score">{palParkData.score}</InfoList.Item>
        </InfoList>
    );
}
