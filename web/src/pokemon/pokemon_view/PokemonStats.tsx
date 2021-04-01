import React, {useContext, useReducer} from 'react';
import AppContext, {AppContextProps} from '../../common/Context';
import {pktQuery} from '../../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../../common/components/Flashes';
import Loading from '../../common/components/Loading';
import InfoList from '../../common/components/InfoList';
import LinearGauge from '../../common/components/gauge/LinearGauge';
import './PokemonStats.scss';

interface PokemonStatsState {
    loadedForPokemon?: number
    stats?: Record<string, ApiRecord.Pokemon.PokemonStatInfo>
    loadingStats: boolean
}

const pokemonStatSlugProperties = [
    ['hp', 'HP'],
    ['attack', 'Attack'],
    ['defense', 'Defense'],
    ['special-attack', 'Special Attack'],
    ['special-defense', 'Special Defense'],
    ['special', 'Special'],
    ['speed', 'Speed'],
    ['total', 'Total'],
];

export default function PokemonStats(props: { pokemon: ApiRecord.Pokemon.Pokemon.PokemonView }) {
    // Setup
    const {pokemon} = props;
    const {setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: PokemonStatsState, newState: Partial<PokemonStatsState>) => ({...state, ...newState}), {
        loadingStats: false,
    } as PokemonStatsState);
    const {stats} = state;

    // Reset
    if (stats && state.loadedForPokemon !== undefined && state.loadedForPokemon !== pokemon.id) {
        setState({stats: undefined, loadedForPokemon: undefined});
    }

    // Load
    if (!state.loadingStats && stats === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Pokemon.PokemonStatInfo>>('pokemon_stats', {
            pokemon: pokemon.id,
            groups: ['pokemon_view'],
        }).then((response) => {
            const stats = Object.fromEntries(response.data['hydra:member'].map(statInfo => [statInfo.stat, statInfo]));
            setState({
                stats: stats,
                loadingStats: false,
                loadedForPokemon: pokemon.id,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading stats.'}]);
        });
        setState({loadingStats: true});
    }

    return (
        <>
            {state.loadingStats && <Loading/>}
            {stats && (
                <InfoList className="pkt-pokemon-view-stats">
                    {pokemonStatSlugProperties.map(([statSlug, statName]) => {
                        if (!stats[statSlug]) {
                            return null;
                        }
                        return (
                            <InfoList.Item key={statSlug} name={statName}>
                                <div className="pkt-pokemon-view-stats-basevalue">
                                    {stats[statSlug].baseValue}
                                </div>
                                <div className="pkt-pokemon-view-stats-percentile-gauge">
                                    <LinearGauge value={stats[statSlug].percentile}/>
                                </div>
                                <div className="pkt-pokemon-view-stats-percentile-value">
                                    (P = {stats[statSlug].percentile})
                                </div>
                            </InfoList.Item>
                        );
                    })}
                </InfoList>
            )}
        </>
    );
}
