import loadComponent from '../common/loadComponent';
import {Route, Switch} from 'react-router-dom';
import {Routes} from '../routes';

const LocationIndex = loadComponent(() => import('./LocationIndex'));
const LocationView = loadComponent(() => import('./LocationView'));

export default function LocationController(props: {}) {
    return (
        <Switch>
            <Route exact path={Routes.LOCATION_VIEW}>
                <LocationView {...props}/>
            </Route>
            <Route exact path={Routes.LOCATION_INDEX}>
                <LocationIndex {...props}/>
            </Route>
        </Switch>
    );
}
