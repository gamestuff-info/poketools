import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {faStar} from '@fortawesome/free-regular-svg-icons';
import {faStar as fasStar} from '@fortawesome/free-solid-svg-icons';
import React from 'react';
import InfoList from '../../common/components/InfoList';
import './PokemonFormPokeathlon.scss';

export default function PokemonFormPokeathlonStats(props: { pokemonForm: ApiRecord.Pokemon.PokemonForm.PokemonView }) {
    const {pokemonForm: {pokeathlonStats}} = props;
    const statStars = new Map(pokeathlonStats.map(pokeathlonStat => {
        const stars = [];
        for (let i = 1; i <= 5; ++i) {
            let star;
            if (i < pokeathlonStat.min || i > pokeathlonStat.max) {
                star = <PokeathlonStatStar variant="outofrange"/>;
            } else if (i === pokeathlonStat.baseValue) {
                star = <PokeathlonStatStar variant="base"/>;
            } else {
                star = <PokeathlonStatStar variant="range"/>;
            }
            stars.push(
                <React.Fragment key={`${pokeathlonStat.pokeathlonStat.slug}-${i}`}>
                    {star}
                </React.Fragment>,
            );
        }
        return [pokeathlonStat.pokeathlonStat.id, stars];
    }));

    return (
        <>
            <InfoList>
                <InfoList.Item name={<PokeathlonStatStar variant="range"/>}>Range</InfoList.Item>
                <InfoList.Item name={<PokeathlonStatStar variant="base"/>}>Base value</InfoList.Item>
            </InfoList>
            <InfoList className="pkt-pokemon-view-pokeathlon">
                {pokeathlonStats.map(pokeathlonStat => (
                    <InfoList.Item key={pokeathlonStat.pokeathlonStat.id} name={pokeathlonStat.pokeathlonStat.name}>
                        {statStars.get(pokeathlonStat.pokeathlonStat.id)}
                    </InfoList.Item>
                ))}
            </InfoList>
        </>
    );
}

function PokeathlonStatStar(props: { variant: 'base' | 'range' | 'outofrange' }) {
    switch (props.variant) {
        case 'base':
            return <FontAwesomeIcon icon={fasStar} className="pkt-pokemon-view-pokeathlon-base"/>;
        case 'range':
            return <FontAwesomeIcon icon={fasStar} className="pkt-pokemon-view-pokeathlon-range"/>;
        case 'outofrange':
            return <FontAwesomeIcon icon={faStar} className="pkt-pokemon-view-pokeathlon-outofrange"/>;
    }
}
