import loadComponent from '../common/loadComponent';
import {Route, Switch} from 'react-router-dom';
import {Routes} from '../routes';

const TypeIndex = loadComponent(() => import('./TypeIndex'));
const TypeView = loadComponent(() => import('./TypeView'));

export default function TypeController(props: {}) {
    return (
        <Switch>
            <Route exact path={Routes.TYPE_INDEX}>
                <TypeIndex/>
            </Route>
            <Route exact path={Routes.TYPE_VIEW}>
                <TypeView/>
            </Route>
        </Switch>
    );
}
