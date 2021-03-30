import React, {useCallback, useContext, useReducer} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {generatePath, Link, useParams} from 'react-router-dom';
import useVersionRedirect from '../common/components/useVersionRedirect';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../common/components/Flashes';
import setPageTitle from '../common/setPageTitle';
import Loading from '../common/components/Loading';
import TypeLabel from './TypeLabel';
import TypeChart from './TypeChart';
import {Breadcrumb} from 'react-bootstrap';
import {Routes} from '../routes';
import DamageClassLabel from '../move/DamageClassLabel';
import MoveTable, {sortFieldMap as moveTableSortFieldMap} from '../move/MoveTable';
import {buildOrderParams} from '../common/components/DataTable';
import TypePokemonTable from './TypePokemonTable';

interface TypeViewProps {
}

interface TypeViewState {
    type?: ApiRecord.Type.Type | null
    loadingType: boolean
    contestType?: ApiRecord.Type.ContestType | null
    loadingContestType: boolean
}

export default function TypeView(props: TypeViewProps) {
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const {type: typeSlug} = useParams<{ version: string, type: string }>();
    const [state, setState] = useReducer((state: TypeViewState, newState: Partial<TypeViewState>) => ({...state, ...newState}), {
        loadingType: false,
        loadingContestType: false,
    } as TypeViewState);
    const {type, loadingType, contestType, loadingContestType} = state;
    let redirect;
    if ((redirect = useVersionRedirect(currentVersion))) {
        return redirect;
    }

    // Reset
    if ((type && typeSlug !== type.slug) || (contestType && typeSlug !== contestType.slug)) {
        setState({
            type: undefined,
            contestType: undefined,
        });
    }

    if (!loadingType && (type === undefined || (type && type.slug !== typeSlug))) {
        // Load type
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Type.Type>>('types', {
            slug: typeSlug,
            page: 1,
            itemsPerPage: 1,
            'groups[]': 'full',
        }, currentVersion).then((response) => {
            if (response.data['hydra:member'].length === 0) {
                setState({
                    type: null,
                    loadingType: false,
                });
            } else {
                setState({
                    type: response.data['hydra:member'][0],
                    loadingType: false,
                });
            }
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading type.'}]);
        });
        setState({loadingType: true});
    }
    if (!loadingContestType && (contestType === undefined || (contestType && contestType.slug !== typeSlug))) {
        // Load contest type if type does not exist
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Type.ContestType>>('contest_types', {
            slug: typeSlug,
            page: 1,
            itemsPerPage: 1,
            'groups[]': 'full',
        }, currentVersion).then((response) => {
            if (response.data['hydra:member'].length === 0) {
                setState({
                    contestType: null,
                    loadingContestType: false,
                });
            } else {
                setState({
                    contestType: response.data['hydra:member'][0],
                    loadingContestType: false,
                });
            }
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading contest type.'}]);
        });
        setState({loadingContestType: true});
    }
    if (type) {
        setPageTitle(['Types', type.name]);
    } else if (contestType) {
        setPageTitle(['Contest Types', contestType.name]);
    }

    return (
        <div>
            <Breadcrumb>
                <Breadcrumb.Item linkAs="span">{currentVersion.name}</Breadcrumb.Item>
                <Breadcrumb.Item linkAs={Link}
                                 linkProps={{to: generatePath(Routes.TYPE_INDEX, {version: currentVersion.slug})}}>
                    Types
                </Breadcrumb.Item>
                <Breadcrumb.Item active>
                    {((loadingType || loadingContestType) || (!type && !contestType)) && <Loading uncontained/>}
                    {type && type.name}
                    {contestType && contestType.name}
                </Breadcrumb.Item>
            </Breadcrumb>

            {((loadingType || loadingContestType) || (!type && !contestType)) && <Loading/>}
            {type && <PokemonTypeView {...props} type={type}/>}
            {contestType && <ContestTypeView {...props} contestType={contestType}/>}
        </div>
    );
}

interface PokemonTypeViewProps extends TypeViewProps {
    type: ApiRecord.Type.Type
}

/**
 * Pokemon/move types
 */
function PokemonTypeView(props: PokemonTypeViewProps) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const {type} = props;

    return (
        <div>
            <h1>
                <TypeLabel type={type} noLink/>
                {!currentVersion.featureSlugs.includes('move-damage-class') && type.damageClass && (
                    <span>&nbsp;(<DamageClassLabel damageClass={type.damageClass}/>)</span>
                )}
            </h1>

            <h2>Efficacy</h2>
            <h3>Attacking</h3>
            <TypeChart attackingType={type.id}/>

            <h3>Defending</h3>
            <TypeChart defendingType={type.id}/>

            <h2>Pokémon</h2>
            <TypePokemonTable type={type}/>

            <h2>Moves</h2>
            <TypeMoves type={type}/>
        </div>
    );
}

function TypeMoves(props: { type: ApiRecord.Type.Type }) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const {type} = props;
    const query = useCallback((pageIndex, pageSize, sortBy) => {
        const params = Object.assign({}, {
            versionGroup: currentVersion.versionGroup,
            page: pageIndex + 1,
            itemsPerPage: pageSize,
            type: type.id,
        }, buildOrderParams(sortBy, moveTableSortFieldMap));
        return pktQuery<ApiRecord.HydraCollection<ApiRecord.Move.MoveInVersionGroup>>('move_in_version_groups', params, currentVersion);
    }, [type, currentVersion]);

    return (
        <MoveTable query={query}/>
    );
}

interface ContestTypeViewProps extends TypeViewProps {
    contestType: ApiRecord.Type.ContestType
}

function ContestTypeView(props: ContestTypeViewProps) {
    const {contestType} = props;

    return (
        <div>
            <h1><TypeLabel type={contestType} noLink/></h1>

            <h2>Moves</h2>
            <ContestTypeMoves contestType={contestType}/>
        </div>
    );
}

function ContestTypeMoves(props: { contestType: ApiRecord.Type.ContestType }) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const {contestType} = props;
    const query = useCallback((pageIndex, pageSize, sortBy) => {
        const params = Object.assign({}, {
            versionGroup: currentVersion.versionGroup,
            page: pageIndex + 1,
            itemsPerPage: pageSize,
            contestType: contestType.id,
        }, buildOrderParams(sortBy, moveTableSortFieldMap));
        return pktQuery<ApiRecord.HydraCollection<ApiRecord.Move.MoveInVersionGroup>>('move_in_version_groups', params, currentVersion);
    }, [contestType, currentVersion]);

    return (
        <MoveTable query={query}/>
    );
}
