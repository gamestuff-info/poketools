import {FlashSeverity} from '../common/components/Flashes';
import useVersionRedirect from '../common/components/useVersionRedirect';
import {Breadcrumb} from 'react-bootstrap';
import React, {useContext, useMemo, useReducer} from 'react';
import {generatePath, Link, useParams} from 'react-router-dom';
import {Routes} from '../routes';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import NotFound from '../common/components/NotFound';
import Loading from '../common/components/Loading';
import setPageTitle from '../common/setPageTitle';
import AppContext, {AppContextProps} from '../common/Context';
import InfoList from '../common/components/InfoList';
import versionHasContests from '../common/versionHasContests';
import TypeLabel from '../type/TypeLabel';
import LinearGauge from '../common/components/gauge/LinearGauge';
import './NatureView.scss';
import NaturePokemonTable from './NaturePokemonTable';

interface NatureViewState {
    loadingNature: boolean
    nature?: ApiRecord.Nature.Nature.NatureView | null
}

export default function NatureView(props: {}) {
    // Setup
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const hasNatures = useMemo(() => currentVersion.featureSlugs.includes('natures'), [currentVersion]);
    const hasBattlePalace = useMemo(() => currentVersion.featureSlugs.includes('battle-palace'), [currentVersion]);
    const hasPokeathlon = useMemo(() => currentVersion.featureSlugs.includes('pokeathlon'), [currentVersion]);
    const {nature: natureSlug} = useParams<{ version: string, nature: string }>();
    const [state, setState] = useReducer((state: NatureViewState, newState: Partial<NatureViewState>) => ({...state, ...newState}), {
        loadingNature: false,
    } as NatureViewState);
    const {nature} = state;
    let redirect;
    if ((redirect = useVersionRedirect(currentVersion))) {
        return redirect;
    }
    if (!hasNatures) {
        return (
            <div>
                <Breadcrumb>
                    <Breadcrumb.Item linkAs="span">{(currentVersion as ApiRecord.Version).name}</Breadcrumb.Item>
                    <Breadcrumb.Item active>Natures</Breadcrumb.Item>
                </Breadcrumb>

                <h1>Natures</h1>
                <p>This version does not have Natures.</p>
            </div>
        );
    }

    // Reset
    if (nature && natureSlug !== nature.slug) {
        setState({nature: undefined});
    }

    // Load
    if (nature === null) {
        return (<NotFound/>);
    } else if (!state.loadingNature && nature === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Nature.Nature.NatureView>>('natures', {
            slug: natureSlug,
            page: 1,
            itemsPerPage: 1,
            groups: ['nature_view'],
        }, currentVersion).then((response) => {
            if (response.data['hydra:member'].length === 0) {
                setState({
                    nature: null,
                    loadingNature: false,
                });
            } else {
                setState({
                    nature: response.data['hydra:member'][0],
                    loadingNature: false,
                });
            }
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading ability.'}]);
        });
        setState({loadingNature: true});
    } else if (nature) {
        setPageTitle(['Natures', nature.name]);
    }

    return (
        <div>
            <Breadcrumb>
                <Breadcrumb.Item linkAs="span">{currentVersion.name}</Breadcrumb.Item>
                <Breadcrumb.Item linkAs={Link}
                                 linkProps={{to: generatePath(Routes.NATURE_INDEX, {version: currentVersion.slug})}}>
                    Natures
                </Breadcrumb.Item>
                <Breadcrumb.Item active>
                    {!nature && <Loading uncontained/>}
                    {nature && nature.name}
                </Breadcrumb.Item>
            </Breadcrumb>

            {state.loadingNature && <Loading/>}
            {nature && (
                <div>
                    <h1>{nature.name}</h1>
                    <NatureStats nature={nature}/>

                    {hasBattlePalace && (
                        <div>
                            <h2>Battle Style Preferences</h2>
                            <BattleStylePrefs nature={nature}/>
                        </div>
                    )}

                    {hasPokeathlon && (
                        <div>
                            <h2>Pokéathlon Stat Modifiers</h2>
                            <PokeathlonStats nature={nature}/>
                        </div>
                    )}

                    <h2>Pokémon</h2>
                    {nature.neutral && (
                        <p>
                            This is a list of Pokémon with similar stats:
                        </p>
                    )}
                    {!nature.neutral && (
                        <p>
                            This is a list of Pokémon with high {nature.statIncreased.name} and
                            low {nature.statDecreased.name}:
                        </p>
                    )}
                    <NaturePokemonTable nature={nature}/>
                </div>
            )}
        </div>
    );
}

function NatureStats(props: { nature: ApiRecord.Nature.Nature.NatureView }) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const hasContests = useMemo(() => versionHasContests(currentVersion), [currentVersion]);
    const {nature} = props;
    if (nature.neutral) {
        return (
            <p>This Nature has no effect on stats.</p>
        );
    }

    return (
        <InfoList>
            <InfoList.Item name="Stat Changes">
                <ul className="list-unstyled">
                    <li>{nature.statIncreased.name} +10%</li>
                    <li>{nature.statDecreased.name} &minus;10%</li>
                </ul>
            </InfoList.Item>
            <InfoList.Item name="Flavor Preferences">
                <ul className="list-unstyled">
                    <li>
                        Likes {nature.flavorLikes.name} {hasContests &&
                    <span>
                        (Helps increase <TypeLabel type={nature.flavorLikes.contestType}/>)
                    </span>}
                    </li>
                    <li>
                        Hates {nature.flavorHates.name} {hasContests &&
                    <span>
                        (Bad for <TypeLabel type={nature.flavorHates.contestType}/>)
                    </span>}
                    </li>
                </ul>
            </InfoList.Item>
        </InfoList>
    );
}

function BattleStylePrefs(props: { nature: ApiRecord.Nature.Nature.NatureView }) {
    const {nature} = props;
    return (
        <InfoList>
            <InfoList.Item name={<span>&ge; 50% HP</span>}>
                <LinearGauge className="pkt-nature-view-battlestyleprefs"
                             value={nature.battleStylePreferences.map(battleStylePref => battleStylePref.highHpChance)}
                />
                <ul className="list-unstyled">
                    {nature.battleStylePreferences.map(battleStylePref => (
                        <li key={`high-${battleStylePref.battleStyle.id}`}>
                            {battleStylePref.highHpChance}% {battleStylePref.battleStyle.name}
                        </li>
                    ))}
                </ul>
            </InfoList.Item>
            <InfoList.Item name={<span>&lt; 50% HP</span>}>
                <LinearGauge className="pkt-nature-view-battlestyleprefs"
                             value={nature.battleStylePreferences.map(battleStylePref => battleStylePref.lowHpChance)}
                />
                <ul className="list-unstyled">
                    {nature.battleStylePreferences.map(battleStylePref => (
                        <li key={`low-${battleStylePref.battleStyle.id}`}>
                            {battleStylePref.lowHpChance}% {battleStylePref.battleStyle.name}
                        </li>
                    ))}
                </ul>
            </InfoList.Item>
        </InfoList>
    );
}

function PokeathlonStats(props: { nature: ApiRecord.Nature.Nature.NatureView }) {
    const {nature} = props;
    return (
        <ul>
            {nature.pokeathlonStatChanges.map(statChange => (
                <li key={statChange.pokeathlonStat.id}>
                    Up to {statChange.maxChange} {statChange.pokeathlonStat.name}
                </li>
            ))}
        </ul>
    );
}
