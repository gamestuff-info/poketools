import React, {useCallback, useContext, useMemo, useReducer} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {generatePath, Link, useParams} from 'react-router-dom';
import NotFound from '../common/components/NotFound';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../common/components/Flashes';
import setPageTitle from '../common/setPageTitle';
import {Breadcrumb, Col, Row} from 'react-bootstrap';
import {Routes} from '../routes';
import Loading from '../common/components/Loading';
import '../assets/styles/MoveView.scss';
import InfoList from '../common/components/InfoList';
import TypeLabel from '../type/TypeLabel';
import DamageClassLabel from './DamageClassLabel';
import useVersionRedirect from '../common/components/useVersionRedirect';
import FlagList from '../common/components/FlagList';
import PktMarkdown from '../common/components/PktMarkdown';
import RepeatedIcon from '../common/components/RepeatedIcon';
import {faHeart as farHeart} from '@fortawesome/free-regular-svg-icons';
import {faHeart} from '@fortawesome/free-solid-svg-icons';
import MoveTable, {sortFieldMap as moveTableSortFieldMap} from './MoveTable';
import {buildOrderParams} from '../common/components/DataTable';
import MovePokemonTable from './MovePokemonTable';

interface MoveViewProps {
}

type PartialMove = Pick<ApiRecord.Move.MoveInVersionGroup, 'id' | 'name' | 'slug'>;

interface MoveViewState {
    move?: ApiRecord.Move.MoveInVersionGroup.MoveView | null
    loadingMove: boolean
}

export default function MoveView(props: MoveViewProps) {
    // Setup
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const {move: moveSlug, version: versionSlug} = useParams<{ version: string, move: string }>();
    const hasContests = useMemo(() => currentVersion.featureSlugs.includes('contests'), [currentVersion]);
    const hasSuperContests = useMemo(() => currentVersion.featureSlugs.includes('super-contests'), [currentVersion]);
    const [state, setState] = useReducer((state: MoveViewState, newState: Partial<MoveViewState>) => ({...state, ...newState}), {
        loadingMove: false,
    } as MoveViewState);
    const {move} = state;

    // Version redirect
    let redirect;
    if ((redirect = useVersionRedirect(currentVersion))) {
        return redirect;
    }

    // Reset
    if (move && (moveSlug !== move.slug || currentVersion.versionGroup !== move.versionGroup)) {
        setState({move: undefined});
    }

    // Load
    if (move === null) {
        return (<NotFound/>);
    } else if (!state.loadingMove && move === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Move.MoveInVersionGroup.MoveView>>('move_in_version_groups', {
            versionGroup: currentVersion.versionGroup,
            slug: moveSlug,
            page: 1,
            itemsPerPage: 1,
            groups: ['move_view'],
        }, currentVersion).then((response) => {
            if (response.data['hydra:member'].length === 0) {
                setState({move: null, loadingMove: false});
            } else {
                setState({move: response.data['hydra:member'][0], loadingMove: false});
            }
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading Move.'}]);
        });
        setState({loadingMove: true});
    } else if (move) {
        setPageTitle(['Moves', move.name]);
    }

    return (
        <div>
            <Breadcrumb>
                <Breadcrumb.Item linkAs="span">{currentVersion.name}</Breadcrumb.Item>
                <Breadcrumb.Item linkAs={Link}
                                 linkProps={{to: generatePath(Routes.MOVE_INDEX, {version: currentVersion.slug})}}>
                    Moves
                </Breadcrumb.Item>
                <Breadcrumb.Item active>
                    {!move && <Loading uncontained/>}
                    {move && move.name}
                </Breadcrumb.Item>
            </Breadcrumb>

            {state.loadingMove && <Loading/>}
            {move && (
                <div>
                    <h1>{move.name}</h1>
                    <p className="pkt-move-view-categories">
                        Categories: {move.categories.map(category => category.name).join(', ')}
                    </p>
                    <p className={`pkt-flavortext pkt-flavortext-${versionSlug}`}>{move.flavorText}</p>

                    <Row>
                        {/* Stats */}
                        <Col md="3">
                            <h2>Stats</h2>
                            <MoveStats move={move}/>
                        </Col>

                        {/* Special Effects */}
                        <Col md>
                            <h2>Special Effects</h2>
                            <MoveSpecialEffects move={move}/>
                        </Col>

                        {/* Flags */}
                        <Col md>
                            <h2>Flags</h2>
                            <MoveFlags move={move}/>
                        </Col>
                    </Row>

                    <h2>Description</h2>
                    <PktMarkdown>
                        {move.effect.description.replaceAll('$effect_chance', String(move.effectChance ?? 0))}
                    </PktMarkdown>

                    {hasContests && (
                        <div>
                            <h2>Contest</h2>
                            <MoveContestInfo move={move}/>
                        </div>
                    )}

                    {hasSuperContests && (
                        <div>
                            <h2>Super Contest</h2>
                            <MoveSuperContestInfo move={move}/>
                        </div>
                    )}

                    <h2>Similar Moves</h2>
                    <p>These moves have the same effect, but their stats and effect chances may differ.</p>
                    <SimilarMoves move={move}/>

                    <h2>Pokémon</h2>
                    <MovePokemonTable move={move}/>
                </div>
            )}
        </div>
    );
}

function MoveStats(props: { move: any }) {
    const {move} = props;

    return (
        <InfoList>
            <InfoList.Item name="Type">
                <TypeLabel type={move.type}/>
            </InfoList.Item>
            <InfoList.Item name="Class">
                <DamageClassLabel damageClass={move.effectiveDamageClass}/>
            </InfoList.Item>
            <InfoList.Item name="Power">
                {move.power ?? '-'}
            </InfoList.Item>
            <InfoList.Item name="Accuracy">
                {move.accuracy ?? '-'}
            </InfoList.Item>
            <InfoList.Item name="PP">
                {move.pp ?? '-'}
            </InfoList.Item>
            <InfoList.Item name="Target">
                {move.target.name}
            </InfoList.Item>
            <InfoList.Item name="Priority">
                {move.priority === 0 && 'Normal'}
                {move.priority !== 0 && move.priority}
            </InfoList.Item>
        </InfoList>
    );
}

function MoveSpecialEffects(props: { move: ApiRecord.Move.MoveInVersionGroup.MoveView }) {
    const {move} = props;
    const specialEffects = useMemo(() => {
        if (!move) {
            return [];
        }
        const list = [];
        // Ailment
        if (move.ailment) {
            list.push(<li key='ailment'>Inflicts {move.ailment.name} on target(s).</li>);
        }
        // Stat changes
        for (const stateChange of move.statChanges) {
            if (stateChange.change < 0) {
                list.push(
                    <li key={`stat-${stateChange.stat.slug}`}>
                        {stateChange.stat.name} decreased by <span className="pkt-value-negative">
                        {stateChange.change * -1}</span>.
                    </li>,
                );
            } else {
                list.push(
                    <li key={`stat-${stateChange.stat.slug}`}>
                        {stateChange.stat.name} increased by <span className="pkt-value-positive">
                        {stateChange.change}</span>.
                    </li>,
                );
            }
        }
        // Crit rate bonus
        if (move.critRateBonus) {
            list.push(<li key="crit-rate">Critical hit rate increased by {move.critRateBonus}.</li>);
        }
        // Multiple hits
        if (move.hits && move.hits !== '1') {
            list.push(<li key="hits">Hits {move.hits} times.</li>);
        }
        // Multiple turns
        if (move.turns && move.turns !== '1') {
            list.push(<li key="turns">Lasts {move.turns} turns.</li>);
        }
        // Recoil
        if (move.recoil) {
            list.push(<li key="recoil">User takes {move.recoil}% of damage given as recoil.</li>);
        }
        // Drain (aka Absorb)
        if (move.drain) {
            list.push(<li key="drain">User heals itself by {move.drain}% of the damage inflicted.</li>);
        }
        // Healing (not to be confused with Drain)
        if (move.healing) {
            if (move.healing < 0) {
                list.push(<li key="healing">User loses {move.healing * -1}% of its max HP.</li>);
            } else {
                list.push(<li key="healing">User heals itself by {move.healing}% of its max HP.</li>);
            }
        }

        return list;
    }, [move]);

    if (specialEffects.length > 0) {
        return (
            <ul>
                {specialEffects}
            </ul>
        );
    } else {
        return (<p>This move has no special effects.</p>);
    }
}

function MoveFlags(props: { move: ApiRecord.Move.MoveInVersionGroup.MoveView }) {
    const {move} = props;

    if (move.flags.length > 0) {
        return (<FlagList flags={move.flags}/>);
    } else {
        return (<p>No special flags apply to this move.</p>);
    }
}

interface MoveContestInfoState {
    loadedInfoForMove?: number
    contestUseBefore?: Array<PartialMove> | null
    loadingContestUseBefore: boolean
    contestUseAfter?: Array<PartialMove> | null
    loadingContestUseAfter: boolean
}

function MoveContestInfo(props: { move: ApiRecord.Move.MoveInVersionGroup.MoveView }) {
    const {move} = props;
    const {setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: MoveContestInfoState, newState: Partial<MoveContestInfoState>) => ({...state, ...newState}), {
        loadingContestUseBefore: false,
        loadingContestUseAfter: false,
    } as MoveContestInfoState);
    const {contestUseBefore, contestUseAfter} = state;

    // Reset
    if (state.loadedInfoForMove !== undefined && move.id !== state.loadedInfoForMove) {
        setState({
            contestUseBefore: undefined,
            contestUseAfter: undefined,
        });
    }

    // Load
    if (!state.loadingContestUseBefore && contestUseBefore === undefined) {
        loadContestCombos(move.id,
            'contest_use_befores',
            () => setState({loadingContestUseBefore: true}),
            (results) => setState({
                contestUseBefore: results,
                loadedInfoForMove: move.id,
                loadingContestUseBefore: false,
            }),
            () => setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading contest combos.'}]));
    }
    if (!state.loadingContestUseAfter && contestUseAfter === undefined) {
        loadContestCombos(move.id,
            'contest_use_afters',
            () => setState({loadingContestUseAfter: true}),
            (results) => setState({
                contestUseAfter: results,
                loadedInfoForMove: move.id,
                loadingContestUseAfter: false,
            }),
            () => setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading contest combos.'}]));
    }

    return (
        <InfoList>
            <InfoList.Item name="Type">
                {props.move.contestType && <TypeLabel type={props.move.contestType}/>}
            </InfoList.Item>
            <InfoList.Item name="Appeal">
                {props.move.contestEffect && (
                    <span aria-label={String(props.move.contestEffect.appeal)}>
                        <RepeatedIcon count={props.move.contestEffect.appeal} icon={farHeart}/>
                    </span>
                )}
            </InfoList.Item>
            <InfoList.Item name="Jam">
                {props.move.contestEffect && (
                    <span aria-label={String(props.move.contestEffect.jam)}>
                        <RepeatedIcon count={props.move.contestEffect.jam} icon={faHeart}/>
                    </span>
                )}
            </InfoList.Item>
            <InfoList.Item name="Game Desc">
                {props.move.contestEffect && props.move.contestEffect.flavorText}
            </InfoList.Item>
            <InfoList.Item name="Use before">
                {state.loadingContestUseBefore && <Loading uncontained/>}
                {contestUseBefore && <ContestMoveList moves={contestUseBefore}/>}
            </InfoList.Item>
            <InfoList.Item name="Use after">
                {state.loadingContestUseAfter && <Loading uncontained/>}
                {contestUseAfter && <ContestMoveList moves={contestUseAfter}/>}
            </InfoList.Item>
        </InfoList>
    );
}

interface MoveSuperContestInfoState {
    loadedInfoForMove?: number
    superContestUseBefore?: Array<PartialMove> | null
    loadingSuperContestUseBefore: boolean
    superContestUseAfter?: Array<PartialMove> | null
    loadingSuperContestUseAfter: boolean
}

function MoveSuperContestInfo(props: { move: ApiRecord.Move.MoveInVersionGroup.MoveView }) {
    const {move} = props;
    const {setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: MoveSuperContestInfoState, newState: Partial<MoveSuperContestInfoState>) => ({...state, ...newState}), {
        loadingSuperContestUseBefore: false,
        loadingSuperContestUseAfter: false,
    } as MoveSuperContestInfoState);
    const {superContestUseBefore, superContestUseAfter} = state;

    // Reset
    if (state.loadedInfoForMove !== undefined && move.id !== state.loadedInfoForMove) {
        setState({
            loadingSuperContestUseBefore: undefined,
            loadingSuperContestUseAfter: undefined,
        });
    }

    // Load
    if (!state.loadingSuperContestUseBefore && superContestUseBefore === undefined) {
        loadContestCombos(move.id,
            'super_contest_use_befores',
            () => setState({loadingSuperContestUseBefore: true}),
            (results) => setState({
                superContestUseBefore: results,
                loadedInfoForMove: move.id,
                loadingSuperContestUseBefore: false,
            }),
            () => setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading contest combos.'}]));
    }
    if (!state.loadingSuperContestUseAfter && superContestUseAfter === undefined) {
        loadContestCombos(move.id,
            'super_contest_use_afters',
            () => setState({loadingSuperContestUseAfter: true}),
            (results) => setState({
                superContestUseAfter: results,
                loadedInfoForMove: move.id,
                loadingSuperContestUseAfter: false,
            }),
            () => setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading contest combos.'}]));
    }

    return (
        <InfoList>
            <InfoList.Item name="Type">
                {props.move.contestType && <TypeLabel type={props.move.contestType}/>}
            </InfoList.Item>
            <InfoList.Item name="Appeal">
                {props.move.superContestEffect && (
                    <span aria-label={String(props.move.superContestEffect.appeal)}>
                        <RepeatedIcon count={props.move.superContestEffect.appeal} icon={farHeart}/>
                    </span>
                )}
            </InfoList.Item>
            <InfoList.Item name="Game Desc">
                {props.move.superContestEffect && props.move.superContestEffect.flavorText}
            </InfoList.Item>
            <InfoList.Item name="Use before">
                {state.loadingSuperContestUseBefore && <Loading uncontained/>}
                {superContestUseBefore && <ContestMoveList moves={superContestUseBefore}/>}
            </InfoList.Item>
            <InfoList.Item name="Use after">
                {state.loadingSuperContestUseAfter && <Loading uncontained/>}
                {superContestUseAfter && <ContestMoveList moves={superContestUseAfter}/>}
            </InfoList.Item>
        </InfoList>
    );
}

function ContestMoveList(props: { moves: Array<PartialMove> }) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    return (
        <ul className="list-unstyled">
            {props.moves.map(move => (
                <li key={move.id}>
                    <Link to={generatePath(Routes.MOVE_VIEW, {version: currentVersion.slug, move: move.slug})}>
                        {move.name}
                    </Link>
                </li>
            ))}
        </ul>
    );
}

/**
 * Fetch matching combos from the server
 * @param moveId
 * @param endpoint the final part of the URI
 * @param startCallback Called when the request is made
 * @param resultsCallback Called when the response is ready
 * @param errorCallback Called when an error occurs.
 */
function loadContestCombos(moveId: number, endpoint: string, startCallback: Function, resultsCallback: ((moves: Array<PartialMove>) => void), errorCallback: Function) {
    pktQuery<ApiRecord.HydraCollection<PartialMove>>(`move_in_version_groups/${moveId}/${endpoint}`, {
        properties: ['id', 'name', 'slug'],
    }).then((response) => {
        resultsCallback(response.data['hydra:member']);
    }).catch((error: AxiosError) => {
        console.log(error.message);
        errorCallback();
    });
}


function SimilarMoves(props: { move: ApiRecord.Move.MoveInVersionGroup.MoveView }) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const {move} = props;
    const query = useCallback((pageIndex, pageSize, sortBy) => {
        const params = Object.assign({}, {
            versionGroup: currentVersion.versionGroup,
            page: pageIndex + 1,
            itemsPerPage: pageSize,
            effect: move.effect.id,
        }, buildOrderParams(sortBy, moveTableSortFieldMap));
        return pktQuery<ApiRecord.HydraCollection<ApiRecord.Move.MoveInVersionGroup>>('move_in_version_groups', params, currentVersion);
    }, [move, currentVersion]);

    return (
        <MoveTable query={query}/>
    );
}
