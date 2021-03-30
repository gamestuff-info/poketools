import React, {useContext, useReducer, useState} from 'react';
import AppContext, {AppContextProps} from '../../common/Context';
import InfoList from '../../common/components/InfoList';
import RadialGauge from '../../common/components/gauge/RadialGauge';
import {pktQuery} from '../../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../../common/components/Flashes';
import {Button, Modal, Table} from 'react-bootstrap';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {faChartLine} from '@fortawesome/free-solid-svg-icons';
import PktMarkdown from '../../common/components/PktMarkdown';
import Loading from '../../common/components/Loading';
import {CartesianGrid, Line, LineChart, XAxis, YAxis} from 'recharts';
import {Link} from 'react-router-dom';
import generateCaptureRateCalcUrl from '../../tools/generateCaptureRateCalcUrl';

const pokemonStatProperties = [
    ['hp', 'HP'],
    ['attack', 'Attack'],
    ['defense', 'Defense'],
    ['specialAttack', 'Special Attack'],
    ['specialDefense', 'Special Defense'],
    ['special', 'Special'],
    ['speed', 'Speed'],
    ['total', 'Total'],
];

export default function PokemonTraining(props: { pokemon: ApiRecord.Pokemon.Pokemon.PokemonView }) {
    const {pokemon} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    return (
        <InfoList>
            <InfoList.Item name="Base Exp">
                {pokemon.experience}
            </InfoList.Item>
            <InfoList.Item name="Effort">
                <InfoList>
                    {pokemonStatProperties.map(([statProperty, statName]) => {
                        if (!pokemon[statProperty] || !pokemon[statProperty].effortChange) {
                            return null;
                        }
                        return (
                            <InfoList.Item key={`effort-${statProperty}`} name={statName}>
                                {(new Intl.NumberFormat()).format(pokemon[statProperty].effortChange)}
                            </InfoList.Item>
                        );
                    })}
                </InfoList>
            </InfoList.Item>
            <InfoList.Item name="Capture Rate">
                <RadialGauge value={Math.round(pokemon.captureRate / 255 * 100)}/>
                <Button as={Link} variant="info" size="sm" className="ml-1"
                        to={generateCaptureRateCalcUrl({pokemonDefending: pokemon}, currentVersion)}
                >
                    Poké Ball Effectiveness
                </Button>
            </InfoList.Item>
            {currentVersion.featureSlugs.includes('happiness') && (
                <InfoList.Item name="Base Happiness">
                    {pokemon.happiness}
                </InfoList.Item>
            )}
            <InfoList.Item name="Growth Rate">
                <PokemonGrowthRate growthRate={pokemon.growthRate}/>
            </InfoList.Item>
        </InfoList>
    );
}

interface PokemonGrowthRateState {
    loadedForGrowthRate?: number
    experience: Array<ApiRecord.Pokemon.LevelExperience>
    loadingExperience: boolean
}

function PokemonGrowthRate(props: { growthRate: ApiRecord.Pokemon.GrowthRate }) {
    // Setup
    const {growthRate} = props;
    const {setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: PokemonGrowthRateState, newState: Partial<PokemonGrowthRateState>) => ({...state, ...newState}), {
        loadingExperience: false,
    } as PokemonGrowthRateState);
    const {experience} = state;
    const [chartVisible, setChartVisible] = useState(false);

    // Reset
    if (experience && state.loadedForGrowthRate !== undefined && state.loadedForGrowthRate !== growthRate.id) {
        setState({experience: undefined, loadedForGrowthRate: undefined});
    }

    // Load
    if (!state.loadingExperience && experience === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Pokemon.LevelExperience>>('level_experiences', {
            growthRate: growthRate.id,
            groups: ['pokemon_view'],
        }).then((response) => {
            setState({
                experience: response.data['hydra:member'],
                loadingExperience: false,
                loadedForGrowthRate: growthRate.id,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading growth rate.'}]);
        });
        setState({loadingExperience: true});
    }

    return (
        <>
            <span>
                {growthRate.name}
                <Button variant="secondary"
                        size="sm"
                        className="ml-1"
                        onClick={() => setChartVisible(true)}
                >
                    <FontAwesomeIcon icon={faChartLine}/>
                </Button>
            </span>

            <Modal scrollable show={chartVisible} onHide={() => setChartVisible(false)}>
                <Modal.Header closeButton>
                    <Modal.Title>{growthRate.name}</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    <PktMarkdown>{growthRate.formula}</PktMarkdown>
                    {state.loadingExperience && <Loading/>}
                    {experience && (
                        <>
                            {/* Chart */}
                            <LineChart width={450}
                                       height={300}
                                       margin={{top: 30, right: 30, left: 30, bottom: 30}}
                                       data={experience}
                            >
                                <CartesianGrid/>
                                <XAxis dataKey="level"
                                       type="number"
                                       label={{value: 'Level', position: 'bottom'}}
                                       allowDecimals={false}
                                />
                                <YAxis label={{value: 'Experience', position: 'left', angle: -90, offset: 20}}/>
                                <Line dataKey="experience"
                                      dot={false}
                                />
                            </LineChart>

                            <Table size="sm">
                                <thead>
                                <tr>
                                    <th scope="col">Level</th>
                                    <th scope="col">Experience</th>
                                </tr>
                                </thead>
                                <tbody>
                                {experience.map(levelExperience => (
                                    <tr key={`level-${levelExperience.level}`}>
                                        <th scope="row">{levelExperience.level}</th>
                                        <td>{levelExperience.experience}</td>
                                    </tr>
                                ))}
                                </tbody>
                            </Table>
                        </>
                    )}
                </Modal.Body>
            </Modal>
        </>
    );
}
