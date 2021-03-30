import {FlashSeverity} from '../common/components/Flashes';
import useVersionRedirect from '../common/components/useVersionRedirect';
import {Breadcrumb} from 'react-bootstrap';
import React, {useContext, useReducer} from 'react';
import {generatePath, Link, useParams} from 'react-router-dom';
import {Routes} from '../routes';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import NotFound from '../common/components/NotFound';
import Loading from '../common/components/Loading';
import PktMarkdown from '../common/components/PktMarkdown';
import setPageTitle from '../common/setPageTitle';
import AppContext, {AppContextProps} from '../common/Context';
import PokemonAbilityTable from './PokemonAbilityTable';

interface AbilityViewProps {
}

interface AbilityViewState {
    ability?: ApiRecord.Ability.AbilityInVersionGroup | null
}

export default function AbilityView(props: AbilityViewProps) {
    // Setup
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const {ability: abilitySlug, version: versionSlug} = useParams<{ version: string, ability: string }>();
    const [state, setState] = useReducer((state: AbilityViewState, newState: Partial<AbilityViewState>) => ({...state, ...newState}), {
        ability: undefined,
    } as AbilityViewState);
    const {ability} = state;
    let redirect;
    if ((redirect = useVersionRedirect(currentVersion))) {
        return redirect;
    }

    // Reset
    if (ability && (abilitySlug !== ability.slug || currentVersion.versionGroup !== ability.versionGroup)) {
        setState({ability: undefined});
    }

    // Load
    if (ability === null) {
        return (<NotFound/>);
    } else if (ability === undefined || (ability && ability.versionGroup !== currentVersion.versionGroup)) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Ability.AbilityInVersionGroup>>('ability_in_version_groups', {
            versionGroup: currentVersion.versionGroup,
            slug: abilitySlug,
            page: 1,
            itemsPerPage: 1,
        }, currentVersion).then((response) => {
            if (response.data['hydra:member'].length === 0) {
                setState({ability: null});
            } else {
                setState({ability: response.data['hydra:member'][0]});
            }
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading ability.'}]);
        });
    } else {
        setPageTitle(['Abilities', ability.name]);
    }

    return (
        <div>
            <Breadcrumb>
                <Breadcrumb.Item linkAs="span">{currentVersion.name}</Breadcrumb.Item>
                <Breadcrumb.Item linkAs={Link}
                                 linkProps={{to: generatePath(Routes.ABILITY_INDEX, {version: currentVersion.slug})}}>
                    Abilities
                </Breadcrumb.Item>
                <Breadcrumb.Item active>
                    {!ability && <Loading uncontained/>}
                    {ability && ability.name}
                </Breadcrumb.Item>
            </Breadcrumb>

            {!ability && <Loading/>}
            {ability && (
                <div>
                    <h1>{ability.name}</h1>
                    <p className={`pkt-flavortext pkt-flavortext-${versionSlug}`}>{ability.flavorText}</p>

                    <h2>Description</h2>
                    <PktMarkdown className="pkt-description">
                        {ability.description}
                    </PktMarkdown>

                    <h2>Pokémon</h2>
                    <PokemonAbilityTable ability={ability}/>
                </div>
            )}
        </div>
    );
}
