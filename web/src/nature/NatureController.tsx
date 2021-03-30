import loadComponent from '../common/loadComponent';
import {Route, Switch} from 'react-router-dom';
import {Routes} from '../routes';

const NatureIndex = loadComponent(() => import('./NatureIndex'));
const NatureView = loadComponent(() => import('./NatureView'));

export default function NatureController(props: {}) {
    return (
        <Switch>
            <Route exact path={Routes.NATURE_VIEW}>
                <NatureView {...props}/>
            </Route>
            <Route exact path={Routes.NATURE_INDEX}>
                <NatureIndex {...props}/>
            </Route>
        </Switch>
    );
}
