import {Breadcrumb} from 'react-bootstrap';
import AbilityTable from './AbilityTable';
import React, {useContext} from 'react';
import useVersionRedirect from '../common/components/useVersionRedirect';
import setPageTitle from '../common/setPageTitle';
import AppContext, {AppContextProps} from '../common/Context';

interface AbilityIndexProps {
}

export default function AbilityIndex(props: AbilityIndexProps) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const hasAbilities = currentVersion.featureSlugs.includes('abilities');
    let redirect;
    if ((redirect = useVersionRedirect(currentVersion as ApiRecord.Version))) {
        return redirect;
    }
    setPageTitle('Abilities');

    return (
        <div>
            <Breadcrumb>
                <Breadcrumb.Item linkAs="span">{(currentVersion as ApiRecord.Version).name}</Breadcrumb.Item>
                <Breadcrumb.Item active>Abilities</Breadcrumb.Item>
            </Breadcrumb>

            <h1>Abilities</h1>
            {hasAbilities && <AbilityTable/>}
            {!hasAbilities && <p>This version does not have Abilities.</p>}
        </div>
    );
}
