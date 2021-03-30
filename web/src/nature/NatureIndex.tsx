import React, {useContext, useReducer} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import useVersionRedirect from '../common/components/useVersionRedirect';
import setPageTitle from '../common/setPageTitle';
import {Breadcrumb, Table} from 'react-bootstrap';
import NatureTable from './NatureTable';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../common/components/Flashes';
import Loading from '../common/components/Loading';
import {faArrowDown, faArrowRight} from '@fortawesome/free-solid-svg-icons';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';

export default function NatureIndex(props: {}) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const hasNatures = currentVersion.featureSlugs.includes('natures');

    // Version redirect
    let redirect;
    if ((redirect = useVersionRedirect(currentVersion as ApiRecord.Version))) {
        return redirect;
    }
    setPageTitle('Natures');

    return (
        <div>
            <Breadcrumb>
                <Breadcrumb.Item linkAs="span">{(currentVersion as ApiRecord.Version).name}</Breadcrumb.Item>
                <Breadcrumb.Item active>Natures</Breadcrumb.Item>
            </Breadcrumb>

            <h1>Natures</h1>
            {!hasNatures && <p>This version does not have Natures.</p>}
            {hasNatures && <NatureTable/>}

            {hasNatures && (
                <div>
                    <h2>Characteristics</h2>
                    <p>Characteristics are the in-game hints to a Pokémon's highest IV and its value.</p>
                    <CharacteristicsTable/>
                </div>
            )}
        </div>
    );
}

interface CharacteristicsTableState {
    /** Map stat id > iv determinator > characteristic */
    characteristics?: Map<number, Map<number, ApiRecord.Nature.Characteristic>>
    ivDeterminators?: Array<number>
    stats?: Array<ApiRecord.Stat>
    loading: boolean
}

function CharacteristicsTable(props: {}) {
    const {setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: CharacteristicsTableState, newState: Partial<CharacteristicsTableState>) => ({...state, ...newState}), {
        loading: false,
    } as CharacteristicsTableState);
    const {characteristics, ivDeterminators, stats} = state;

    // Load
    if (!state.loading && (characteristics === undefined || ivDeterminators === undefined || stats === undefined)) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Nature.Characteristic>>('characteristics', {
            pagination: 0,
        }).then(response => {
            // Map characteristics
            const characteristicData = new Map();
            for (const characteristic of response.data['hydra:member']) {
                if (!characteristicData.has(characteristic.stat.id)) {
                    characteristicData.set(characteristic.stat.id, new Map());
                }
                characteristicData.get(characteristic.stat.id).set(characteristic.ivDeterminator, characteristic);
            }

            // IV Determinators
            const uniqueDeterminators = new Set(response.data['hydra:member'].map(characteristic => characteristic.ivDeterminator));
            const determinators = Array.from(uniqueDeterminators);
            determinators.sort((a, b) => a - b);

            // Stats
            const uniqueStats = new Map(response.data['hydra:member'].map(characteristic => [characteristic.stat.id, characteristic.stat]));
            const stats = Array.from(uniqueStats.values());
            stats.sort((a, b) => a.position - b.position);
            setState({
                loading: false,
                characteristics: characteristicData,
                ivDeterminators: determinators,
                stats: stats,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading Characteristics.'}]);
        });
        setState({loading: true});
    }

    return (
        <div>
            {state.loading && <Loading/>}
            {characteristics && ivDeterminators && stats && (
                <Table responsive bordered className="pkt-table-map">
                    <thead>
                    <tr>
                        <th className="text-nowrap border-bottom-0" scope="row">
                            Last Digit <FontAwesomeIcon icon={faArrowRight} aria-hidden/>
                        </th>
                        {ivDeterminators.map(ivDeterminator => (
                            <th key={ivDeterminator} rowSpan={2} scope="col">
                                {ivDeterminator} or {ivDeterminator + 5}
                            </th>
                        ))}
                    </tr>
                    <tr>
                        <th className="text-nowrap border-top-0" scope="col">
                            Stat <FontAwesomeIcon icon={faArrowDown} aria-hidden/>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {stats.map(stat => (
                        <tr key={stat.id}>
                            <th scope="row">{stat.name}</th>
                            {Array.from((characteristics.get(stat.id) as Map<number, ApiRecord.Nature.Characteristic>).entries()).map(([ivDeterminator, characteristic]) => (
                                <td key={`${stat.id}-${ivDeterminator}`}>{characteristic.flavorText}</td>
                            ))}
                        </tr>
                    ))}
                    </tbody>
                </Table>
            )}
        </div>
    );
}
