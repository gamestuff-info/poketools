import loadComponent from '../common/loadComponent';
import {Route, Switch} from 'react-router-dom';
import {Routes} from '../routes';

const ItemIndex = loadComponent(() => import('./ItemIndex'));
const ItemView = loadComponent(() => import('./ItemView'));

export default function AbilityController(props: {}) {
    return (
        <Switch>
            <Route exact path={Routes.ITEM_INDEX}>
                <ItemIndex {...props}/>
            </Route>
            <Route exact path={Routes.ITEM_VIEW}>
                <ItemView {...props}/>
            </Route>
        </Switch>
    );
}
