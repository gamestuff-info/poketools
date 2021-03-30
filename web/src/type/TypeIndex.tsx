import {Breadcrumb, Table} from 'react-bootstrap';
import React, {useContext, useMemo, useReducer} from 'react';
import useVersionRedirect from '../common/components/useVersionRedirect';
import setPageTitle from '../common/setPageTitle';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import Loading from '../common/components/Loading';
import TypeLabel from './TypeLabel';
import '../assets/styles/TypeIndex.scss';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {faArrowDown, faArrowRight} from '@fortawesome/free-solid-svg-icons';
import TypeEfficacy from './TypeEfficacy';
import AppContext, {AppContextProps} from '../common/Context';
import {FlashSeverity} from '../common/components/Flashes';
import versionHasContests from '../common/versionHasContests';

interface TypeIndexProps {
}

interface TypeIndexState {
    loadedVersionGroupId?: string
    typeChartId?: number | null
    loadingTypeChart: boolean
    contestTypes?: Array<ApiRecord.Type.ContestType> | null
    loadingContestTypes: boolean
}

export default function TypeIndex(props: TypeIndexProps) {
    setPageTitle('Types');
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const hasContests = useMemo(() => versionHasContests(currentVersion), [currentVersion]);
    const [state, setState] = useReducer((state: TypeIndexState, newState: Partial<TypeIndexState>) => ({...state, ...newState}), {
        loadingTypeChart: false,
        contestTypes: hasContests ? undefined : [],
    } as TypeIndexState);
    const {loadedVersionGroupId, typeChartId, loadingTypeChart, contestTypes, loadingContestTypes} = state;
    let redirect;
    if ((redirect = useVersionRedirect(currentVersion))) {
        return redirect;
    }

    // Get version's type chart
    const versionGroup = currentVersion.versionGroup;
    if (!loadingTypeChart && (typeChartId === undefined || loadedVersionGroupId !== versionGroup)) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Type.TypeChart>>('type_charts', {
            versionGroups: versionGroup,
        }).then((response) => {
            if (response.data['hydra:member'].length === 0) {
                setState({
                    loadedVersionGroupId: versionGroup,
                    typeChartId: null,
                });
            } else {
                setState({
                    loadedVersionGroupId: versionGroup,
                    typeChartId: response.data['hydra:member'][0].id,
                    loadingTypeChart: false,
                });
            }
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading types.'}]);
        });
        setState({loadingTypeChart: true});
    }


    // Get contest types
    if (!loadingContestTypes && contestTypes === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Type.ContestType>>('contest_types', {pagination: false})
            .then((response) => {
                    setState({
                        contestTypes: response.data['hydra:member'],
                        loadingContestTypes: false,
                    });
                },
            ).catch((error: AxiosError) => {
                console.log(error.message);
                setState({
                    contestTypes: null,
                    loadingContestTypes: false,
                });
            },
        );
        setState({loadingContestTypes: true});
    }

    return (
        <div>
            <Breadcrumb>
                <Breadcrumb.Item linkAs="span">{currentVersion.name}</Breadcrumb.Item>
                <Breadcrumb.Item active>Types</Breadcrumb.Item>
            </Breadcrumb>

            <h1>Types</h1>
            {!loadingTypeChart && typeChartId &&
            <TypeChart {...props} typeChartId={typeChartId}/>}
            {(loadingTypeChart || !typeChartId) && <Loading/>}

            {hasContests && (
                <div>
                    <h2>Contest Types</h2>
                    {(loadingContestTypes || !contestTypes) && <Loading/>}
                    {!loadingContestTypes && contestTypes && (
                        <ul className="list-inline">
                            {contestTypes.map(contestType => (
                                <li key={contestType.slug} className="list-inline-item">
                                    <TypeLabel type={contestType}/>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            )}
        </div>
    );
}

interface TypeChartProps extends TypeIndexProps {
    typeChartId: number
}

interface TypeChartState {
    loadedTypeChartId?: number
    allTypes?: Array<ApiRecord.Type.Type> | null
    loadingTypes: boolean
    efficacies?: TypeEfficacyMap | null
    loadingEfficacies: boolean
}

function TypeChart(props: TypeChartProps) {
    const {setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: TypeChartState, newState: Partial<TypeChartState>) => ({...state, ...newState}), {
        loadingTypes: false,
        loadingContestTypes: false,
        loadingEfficacies: false,
    } as TypeChartState);
    const {typeChartId} = props;
    const {
        loadedTypeChartId,
        allTypes,
        loadingTypes,
        efficacies,
        loadingEfficacies,
    } = state;

    // Get all types
    if (!loadingTypes && allTypes === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Type.Type>>('types', {hidden: 0, pagination: false})
            .then((response) => {
                    setState({
                        allTypes: response.data['hydra:member'],
                        loadingTypes: false,
                    });
                },
            ).catch((error: AxiosError) => {
                console.log(error.message);
                setState({
                    allTypes: null,
                    loadingTypes: false,
                });
            },
        );
        setState({loadingTypes: true});
    }

    // Get efficacies for type chart
    // The types list is filtered from efficacies, so don't act until types are available.
    if (!loadingEfficacies && allTypes && (efficacies === undefined || loadedTypeChartId !== typeChartId)) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Type.TypeEfficacy>>('type_efficacies', {
            typeChart: typeChartId,
            pagination: false,
            properties: ['attackingType', 'defendingType', 'efficacy'],
        })
            .then((response) => {
                setState({
                    loadedTypeChartId: typeChartId,
                    efficacies: buildEfficacyMap(response.data['hydra:member']),
                    loadingEfficacies: false,
                });
            }).catch((error: AxiosError) => {
                console.log(error.message);
                setState({
                    loadedTypeChartId: typeChartId,
                    efficacies: null,
                    loadingEfficacies: false,
                });
            },
        );
        setState({loadingEfficacies: true});
    }

    // Filter types
    const types = useMemo(() => {
        if (efficacies && allTypes) {
            return extractTypesFromEfficacies(efficacies, allTypes);
        }
        return undefined;
    }, [efficacies, allTypes]);

    // Error handling
    if (allTypes === null || efficacies === null) {
        setFlashes([{severity: FlashSeverity.DANGER, message: 'Error fetching types.'}]);
        return null;
    }

    const isReady = efficacies !== undefined && types !== undefined;
    return (
        <div>
            {!isReady && <Loading/>}
            {isReady &&
            <Table responsive className="pkt-type-index-typechart pkt-table-map" id="pkt-type-index-typechart">
                <thead>
                <tr>
                    <th scope="row"
                        className="text-nowrap border-bottom-0"
                    >
                        Defending <FontAwesomeIcon icon={faArrowRight} aria-hidden/>
                    </th>
                    {(types as Array<ApiRecord.Type.Type>).map((type) => (
                        <th key={type.id}
                            scope="col"
                            rowSpan={2}
                            className="pkt-type-index-typechart-defending"
                            data-defending-type={type.id}
                            onMouseOver={onHover}
                        >
                            <TypeLabel type={type}/>
                        </th>
                    ))}
                </tr>
                <tr>
                    <th scope="col" className="text-nowrap border-top-0">
                        Attacking <FontAwesomeIcon icon={faArrowDown} aria-hidden/>
                    </th>
                </tr>
                </thead>
                <tbody>
                {(types as Array<ApiRecord.Type.Type>).map((attackingType) => (
                    <tr key={attackingType.id}>
                        <th scope="row"
                            className="pkt-type-index-typechart-attacking"
                            data-attacking-type={attackingType.id}
                            onMouseOver={onHover}
                        >
                            <TypeLabel type={attackingType}/>
                        </th>
                        {(types as Array<ApiRecord.Type.Type>).map((defendingType) => {
                            const classes = ['pkt-type-index-typechart-efficacy'];
                            if (attackingType.id === defendingType.id) {
                                classes.push('pkt-type-index-typechart-sametype');
                            }
                            const efficacy = (efficacies as TypeEfficacyMap)[attackingType['@id']][defendingType['@id']];
                            return (
                                <td key={defendingType.id}
                                    className={classes.join(' ')}
                                    data-attacking-type={attackingType.id}
                                    data-defending-type={defendingType.id}
                                    onMouseOver={onHover}
                                >
                                    <TypeEfficacy efficacy={efficacy}/>
                                </td>
                            );
                        })}
                    </tr>
                ))}
                </tbody>
            </Table>}
        </div>
    );
}

/**
 * Table cell hover event handler
 * @param e
 */
function onHover(e: React.MouseEvent) {
    // Cover the case where the mouseOver event is on a different tag (e.g. span or a) and not the cell directly.
    let cell = e.target as HTMLElement;
    const tableCellTags = ['th', 'td'];
    while (!tableCellTags.includes(cell.tagName.toLowerCase())) {
        if (cell.parentElement === null) {
            throw new Error('Error finding hovered cell');
        }
        cell = cell.parentElement;
    }
    const table = document.getElementById('pkt-type-index-typechart') as HTMLTableElement;
    const {attackingType, defendingType} = cell.dataset;

    // Highlight matching row/column
    const allCells = table.querySelectorAll<HTMLTableCellElement>('th, td');
    for (const otherCell of allCells) {
        const {attackingType: otherAttackingType, defendingType: otherDefendingType} = otherCell.dataset;
        otherCell.classList.toggle('pkt-hover', attackingType === otherAttackingType || defendingType === otherDefendingType);
    }
}

/**
 * Extract used types from a list of efficacies.
 *
 * @param efficacies
 * @param allTypes
 */
function extractTypesFromEfficacies(efficacies: TypeEfficacyMap, allTypes: Array<ApiRecord.Type.Type>) {
    return allTypes.filter((type) => type['@id'] in efficacies && !type.hidden);
}

/**
 * Create a map from a list of efficacies.
 * @param efficacies
 * @return A map with attacking type IRI > defending type IRI > efficacy
 */
function buildEfficacyMap(efficacies: Array<ApiRecord.Type.TypeEfficacy>) {
    const efficacyMap: TypeEfficacyMap = {};
    for (const efficacy of efficacies) {
        if (!(efficacy.attackingType in efficacyMap)) {
            efficacyMap[efficacy.attackingType] = {};
        }
        efficacyMap[efficacy.attackingType][efficacy.defendingType] = efficacy.efficacy;
    }

    return efficacyMap;
}
