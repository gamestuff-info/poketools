import {Tab, Tabs} from 'react-bootstrap';
import InfoList from '../../common/components/InfoList';
import PokemonColorLabel from '../PokemonColorLabel';
import PokemonHabitatLabel from '../PokemonHabitatLabel';
import {AssetPackage, getAssetUrl} from '../../common/getAssetUrl';
import PokemonShapeLabel from '../PokemonShapeLabel';
import React, {useMemo} from 'react';
import {unit} from 'mathjs';

export default function PokemonFormMedia(props: { pokemon: ApiRecord.Pokemon.Pokemon.PokemonView, form: ApiRecord.Pokemon.PokemonForm.PokemonView }) {
    const {pokemon, form} = props;
    return (
        <Tabs defaultActiveKey="sprites">
            {/* Sprites */}
            <Tab eventKey="sprites" title="Sprites">
                {form.sprites.map((sprite, index) => (
                    <PokemonFormSprite key={`sprite-${index}`} sprite={sprite}/>
                ))}
            </Tab>

            {/* Pokedex flavor */}
            <Tab eventKey="meta" title="Meta">
                <InfoList>
                    {pokemon.color && (
                        <InfoList.Item name="Color">
                            <PokemonColorLabel color={pokemon.color}/>
                        </InfoList.Item>
                    )}
                    {pokemon.habitat && (
                        <InfoList.Item name="Habitat">
                            <PokemonHabitatLabel habitat={pokemon.habitat}/>
                        </InfoList.Item>
                    )}
                    {form.footprint && (
                        <InfoList.Item name="Footprint">
                            <img src={getAssetUrl(`pokemon/footprint/${form.footprint}`, AssetPackage.MEDIA)} alt=""/>
                        </InfoList.Item>
                    )}
                    {pokemon.shape && (
                        <InfoList.Item name="Shape">
                            <PokemonShapeLabel shape={pokemon.shape}/>
                        </InfoList.Item>
                    )}
                    {pokemon.heightCentimeters && (
                        <InfoList.Item name="Height">
                            <PokemonHeight heightCentimeters={pokemon.heightCentimeters}/>
                        </InfoList.Item>
                    )}
                    {pokemon.weightGrams && (
                        <InfoList.Item name="Weight">
                            <PokemonWeight weightGrams={pokemon.weightGrams}/>
                        </InfoList.Item>
                    )}
                    {form.cry && (
                        <InfoList.Item name="Cry">
                            <PokemonFormCry cry={form.cry}/>
                        </InfoList.Item>
                    )}
                </InfoList>
            </Tab>
        </Tabs>
    );
}

function PokemonFormSprite(props: { sprite: ApiRecord.Pokemon.PokemonSprite }) {
    const {sprite} = props;
    if (sprite.url?.slice(-4) === 'webm') {
        return <video src={getAssetUrl(`pokemon/sprite/${sprite.url}`, AssetPackage.MEDIA)}
                      className="img-thumbnail"
                      loop
                      preload="auto"
        />;
    }
    return <img src={getAssetUrl(`pokemon/sprite/${sprite.url}`, AssetPackage.MEDIA)}
                alt=""
                className="img-thumbnail"
    />;
}

function PokemonHeight(props: { heightCentimeters: number }) {
    const {heightCentimeters} = props;
    const height = useMemo(() => {
        const measurement = unit(Math.round(heightCentimeters), 'cm');
        const [feet, inches] = measurement.splitUnit(['ft', 'in']);
        return [
            measurement.toString(),
            `${feet.format({notation: 'fixed', precision: 0})}, ${inches.format({notation: 'fixed', precision: 2})}`,
        ].join(' / ');
    }, [heightCentimeters]);

    return (<span>{height}</span>);
}

function PokemonWeight(props: { weightGrams: number }) {
    const {weightGrams} = props;
    const weight = useMemo(() => {
        const measurement = unit(Math.round(weightGrams), 'g');
        const [pounds, ounces] = measurement.splitUnit(['lb', 'oz']);
        return [
            measurement.toString(),
            `${pounds.format({notation: 'fixed', precision: 0})}, ${ounces.format({notation: 'fixed', precision: 2})}`,
        ].join(' / ');
    }, [weightGrams]);

    return (<span>{weight}</span>);
}

function PokemonFormCry(props: { cry: string }) {
    const {cry} = props;

    // TODO: Simplify the player with a custom control
    return (
        <audio src={getAssetUrl(`pokemon/cry/${cry}`, AssetPackage.MEDIA)}
               controls
               preload="auto"
        />
    );
}
