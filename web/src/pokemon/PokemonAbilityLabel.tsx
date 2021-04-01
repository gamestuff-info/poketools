import React, {useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {generatePath, Link} from 'react-router-dom';
import {Routes} from '../routes';
import './PokemonAbilityLabel.scss';
import {OverlayTrigger, Tooltip} from 'react-bootstrap';

function HiddenAbilityTooltip(props: {}) {
    return (
        <Tooltip id={'hidden-ability-tooltip'} {...props}>
            This ability is only present under special conditions.
        </Tooltip>
    );
}

export default function PokemonAbilityLabel(props: { ability: ApiRecord.Pokemon.PokemonAbility }) {
    const {ability} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    let label;
    if (ability.hidden) {
        label = (
            <OverlayTrigger overlay={HiddenAbilityTooltip} placement="auto">
                <span className='pkt-pokemon-ability-hidden'>
                    *{ability.ability.name}
                </span>
            </OverlayTrigger>
        );
    } else {
        label = (<span>{ability.ability.name}</span>);
    }
    return (
        <Link to={generatePath(Routes.ABILITY_VIEW, {
            version: currentVersion.slug,
            ability: ability.ability.slug,
        })}
              className="text-nowrap"
        >
            {label}
        </Link>
    );
}
