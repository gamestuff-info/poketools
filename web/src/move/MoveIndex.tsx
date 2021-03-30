import React, {useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import useVersionRedirect from '../common/components/useVersionRedirect';
import setPageTitle from '../common/setPageTitle';
import {Breadcrumb} from 'react-bootstrap';
import MoveTable from './MoveTable';

export default function MoveIndex(props: {}) {
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    // Version redirect
    let redirect;
    if ((redirect = useVersionRedirect(currentVersion as ApiRecord.Version))) {
        return redirect;
    }
    setPageTitle('Moves');

    return (
        <div>
            <Breadcrumb>
                <Breadcrumb.Item linkAs="span">{(currentVersion as ApiRecord.Version).name}</Breadcrumb.Item>
                <Breadcrumb.Item active>Moves</Breadcrumb.Item>
            </Breadcrumb>

            <h1>Moves</h1>
            <MoveTable/>
        </div>
    );
}
