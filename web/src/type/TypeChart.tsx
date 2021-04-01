import {useContext, useReducer} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import {Table} from 'react-bootstrap';
import Loading from '../common/components/Loading';
import TypeLabel from './TypeLabel';
import TypeEfficacy from './TypeEfficacy';
import './TypeChart.scss';
import {FlashSeverity} from '../common/components/Flashes';

type EntityId = string | number;

interface AttackingTypeChartProps {
    attackingType: EntityId
    defendingType?: undefined
}

interface DefendingTypeChartProps {
    attackingType?: undefined
    defendingType: EntityId | [EntityId, EntityId?]
}

type TypeChartProps = AttackingTypeChartProps | DefendingTypeChartProps;

interface TypeChartState {
    loadedForVersionGroup?: string
    efficacies?: Array<ApiRecord.Type.TypeDamage> | null
    loadingEfficacies: boolean
    loadedAttackingType?: EntityId
    loadedDefendingType?: EntityId | [EntityId, EntityId?]
}

export default function TypeChart(props: TypeChartProps) {
    const [state, setState] = useReducer((state: TypeChartState, newState: Partial<TypeChartState>) => ({...state, ...newState}), {
        loadingEfficacies: false,
    } as TypeChartState);
    let {attackingType, defendingType} = Object.assign({}, {
        attackingType: null,
        defendingType: null,
    }, props) as Required<TypeChartProps>;
    const {setFlashes, currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const {efficacies} = state;
    // Get all types
    // Get efficacies for type chart
    if (!state.loadingEfficacies && (efficacies === undefined
        || (state.loadedForVersionGroup && currentVersion.versionGroup !== state.loadedForVersionGroup)
        || state.loadedAttackingType !== attackingType
        || state.loadedDefendingType !== defendingType)) {
        const params: Record<string, any> = {
            versionGroup: currentVersion.versionGroup,
        };
        if (attackingType) {
            params.attackingType = attackingType;
        } else if (defendingType) {
            params.defendingType = defendingType;
        }
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Type.TypeDamage>>('type_damages', params)
            .then((response) => {
                setState({
                    loadedForVersionGroup: currentVersion.versionGroup,
                    efficacies: response.data['hydra:member'],
                    loadingEfficacies: false,
                    loadedAttackingType: attackingType,
                    loadedDefendingType: defendingType,
                });
            }).catch((error: AxiosError) => {
                console.log(error.message);
                setState({
                    loadedForVersionGroup: currentVersion.versionGroup,
                    efficacies: null,
                    loadingEfficacies: false,
                    loadedAttackingType: attackingType,
                    loadedDefendingType: defendingType,
                });
            },
        );
        setState({loadingEfficacies: true});
    }

    // Error handling
    if (efficacies === null) {
        setFlashes([{severity: FlashSeverity.DANGER, message: 'Error fetching type matchups.'}]);
        return null;
    }

    return (
        <div>
            {state.loadingEfficacies && <Loading/>}
            {efficacies && <div>
                <Table responsive className="pkt-type-chart-matchup d-none d-md-block">
                    <thead>
                    <tr>
                        {efficacies.map(typeDamage => <TypeChartTypeName key={`type_${typeDamage.type.id}`}
                                                                         typeDamage={typeDamage}/>)}
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        {efficacies.map(typeDamage => <TypeChartEfficacy key={`eff_${typeDamage.type.id}`}
                                                                         typeDamage={typeDamage}/>)}
                    </tr>
                    </tbody>
                </Table>

                {/* Vertical format, for smaller screens */}
                <Table responsive className="d-md-none pkt-type-chart-matchup pkt-type-chart-matchup-vertical">
                    <tbody>
                    {efficacies.map(typeDamage => (
                        <tr key={typeDamage.type.id}>
                            <TypeChartTypeName typeDamage={typeDamage}/>
                            <TypeChartEfficacy typeDamage={typeDamage}/>
                        </tr>
                    ))}
                    </tbody>
                </Table>
            </div>}
        </div>
    );
}

function TypeChartTypeName(props: { typeDamage: ApiRecord.Type.TypeDamage }) {
    const {typeDamage} = props;
    return (
        <th className="pkt-type-chart-type">
            <TypeLabel type={typeDamage.type}/>
        </th>
    );
}

function TypeChartEfficacy(props: { typeDamage: ApiRecord.Type.TypeDamage }) {
    const {typeDamage} = props;
    return (
        <td className="pkt-type-chart-efficacy">
            <TypeEfficacy efficacy={typeDamage.efficacy}/>
        </td>
    );
}
