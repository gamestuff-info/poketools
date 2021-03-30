import loadComponent from '../common/loadComponent';
import {Route, Switch} from 'react-router-dom';
import {Routes} from '../routes';

const AbilityIndex = loadComponent(() => import('./AbilityIndex'));
const AbilityView = loadComponent(() => import('./AbilityView'));

export default function AbilityController(props: {}) {
    return (
        <Switch>
            <Route exact path={Routes.ABILITY_VIEW}>
                <AbilityView {...props}/>
            </Route>
            <Route exact path={Routes.ABILITY_INDEX}>
                <AbilityIndex {...props}/>
            </Route>
        </Switch>
    );
}
