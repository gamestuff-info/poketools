import React, {useContext} from 'react';
import AppContext, {AppContextProps} from '../../common/Context';
import {generatePath, Link} from 'react-router-dom';
import {Routes} from '../../routes';
import RepeatedIcon from '../../common/components/RepeatedIcon';
import {faArrowUp} from '@fortawesome/free-solid-svg-icons';
import {Table} from 'react-bootstrap';
import '../../assets/styles/PokemonHatchSteps.scss';

export default function PokemonHatchSteps(props: { pokemon: ApiRecord.Pokemon.Pokemon.PokemonView }) {
    const {pokemon: {hatchSteps}} = props;
    if (!hatchSteps) {
        return null;
    }

    return (<HatchSteps eggCycles={hatchSteps}/>);
}

interface HatchStepsProps {
    eggCycles: number
}

function HatchSteps(props: HatchStepsProps) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    switch (currentVersion.generationNumber) {
        case 2:
            return <Gen2HatchSteps {...props}/>;
        case 3:
            return <Gen3HatchSteps {...props}/>;
        case 4:
            return <Gen4HatchSteps {...props}/>;
        case 5:
        case 6:
            return <Gen56HatchSteps {...props}/>;
        default:
            return <Gen7HatchSteps {...props}/>;
    }
}

function Gen2HatchSteps(props: HatchStepsProps) {
    const {eggCycles} = props;
    const stepsPerCycle = 256;

    return (<HatchStepsRange stepsPerCycle={stepsPerCycle} cycles={eggCycles}/>);
}

function Gen3HatchSteps(props: HatchStepsProps) {
    const eggCycles = props.eggCycles + 1;
    const stepsPerCycle = 256;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    if (currentVersion.slug === 'emerald') {
        return (
            <Table size="sm" className="pkt-pokemon-hatchsteps">
                <tbody>
                <tr>
                    <th scope="row">Normally</th>
                    <td><HatchStepsRange stepsPerCycle={stepsPerCycle} cycles={eggCycles}/></td>
                </tr>
                <tr>
                    <th scope="row">With <HalfCycles/></th>
                    <td><HatchStepsRange stepsPerCycle={stepsPerCycle} cycles={Math.floor(eggCycles / 2)}/></td>
                </tr>
                </tbody>
            </Table>
        );
    }

    return (<HatchStepsRange stepsPerCycle={stepsPerCycle} cycles={eggCycles}/>);
}

function Gen4HatchSteps(props: HatchStepsProps) {
    const eggCycles = props.eggCycles + 1;
    const stepsPerCycle = 255;

    return (
        <Table size="sm" className="pkt-pokemon-hatchsteps">
            <tbody>
            <tr>
                <th scope="row">Normally</th>
                <td><HatchStepsRange stepsPerCycle={stepsPerCycle} cycles={eggCycles}/></td>
            </tr>
            <tr>
                <th scope="row">With <HalfCycles/></th>
                <td><HatchStepsRange stepsPerCycle={stepsPerCycle} cycles={Math.floor(eggCycles / 2)}/></td>
            </tr>
            </tbody>
        </Table>
    );
}

function Gen56HatchSteps(props: HatchStepsProps) {
    const {eggCycles} = props;
    const stepsPerCycle = 257;

    const hatchingPowerLevels = [1, 2, 3];
    const hatchingPowerMultipliers = new Map(
        [
            [1, 0.875],
            [2, 0.75],
            [3, 0.5],
        ],
    );

    return (
        <Table size="sm" className="pkt-pokemon-hatchsteps">
            <tbody>
            <tr>
                <th scope="row">Normally</th>
                <td><HatchStepsRange stepsPerCycle={stepsPerCycle} cycles={eggCycles}/></td>
            </tr>
            <tr>
                <th scope="row">With <HalfCycles/></th>
                <td><HatchStepsRange stepsPerCycle={stepsPerCycle} cycles={Math.floor(eggCycles / 2)}/></td>
            </tr>
            {hatchingPowerLevels.map(level => (
                <React.Fragment key={`hatching-power-${level}`}>
                    <tr>
                        <th scope="row">With <HatchingPower level={level}/></th>
                        <td>
                            <HatchStepsRange
                                stepsPerCycle={Math.floor(stepsPerCycle * (hatchingPowerMultipliers.get(level) as number))}
                                cycles={eggCycles}/>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">With <HatchingPower level={level}/> and <HalfCycles/></th>
                        <td>
                            <HatchStepsRange
                                stepsPerCycle={Math.floor(stepsPerCycle * (hatchingPowerMultipliers.get(level) as number))}
                                cycles={Math.floor(eggCycles / 2)}/>
                        </td>
                    </tr>
                </React.Fragment>
            ))}
            </tbody>
        </Table>
    );
}


function Gen7HatchSteps(props: HatchStepsProps) {
    const {eggCycles} = props;
    const stepsPerCycle = 256;

    return (
        <Table size="sm" className="pkt-pokemon-hatchsteps">
            <tbody>
            <tr>
                <th scope="row">Normally</th>
                <td><HatchStepsRange stepsPerCycle={stepsPerCycle} cycles={eggCycles}/></td>
            </tr>
            <tr>
                <th scope="row">With <HalfCycles/></th>
                <td><HatchStepsRange stepsPerCycle={stepsPerCycle} cycles={Math.floor(eggCycles / 2)}/></td>
            </tr>
            </tbody>
        </Table>
    );
}

function HalfCycles(props: {}) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    return (
        <>
            <Link to={generatePath(Routes.ABILITY_VIEW, {version: currentVersion.slug, ability: 'flame-body'})}>
                Flame Body
            </Link>
            /
            <Link to={generatePath(Routes.ABILITY_VIEW, {version: currentVersion.slug, ability: 'magma-armor'})}>
                Magma Armor
            </Link>
            {/* Omega Ruby and Alpha Sapphire have a feature that acts the same as the hot body abilities. */}
            {['omega-ruby', 'alpha-sapphire'].includes(currentVersion.slug) && ' or Secret Pal with "Take care of an Egg"'}
        </>
    );
}

function HatchingPower(props: { level: number }) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    let levelLabel = null;
    if (currentVersion.generationNumber === 5) {
        levelLabel = (
            <>
                <RepeatedIcon count={props.level} icon={faArrowUp}/>
                <span className="sr-only">{props.level}</span>
            </>
        );
    } else {
        levelLabel = `Lv ${props.level}`;
    }

    return (<>Hatching Power {levelLabel}</>);
}

function HatchStepsRange(props: { stepsPerCycle: number, cycles: number }) {
    const {stepsPerCycle, cycles} = props;

    return (
        <span className="text-nowrap">
            {stepsPerCycle * cycles - (stepsPerCycle - 1)}&ndash;{stepsPerCycle * cycles}
        </span>
    );
}
