import loadComponent from '../common/loadComponent';
import {Route, Switch} from 'react-router-dom';
import {Routes} from '../routes';

const MoveIndex = loadComponent(() => import('./MoveIndex'));
const MoveView = loadComponent(() => import('./MoveView'));

export default function MoveController(props: {}) {
    return (
        <Switch>
            <Route exact path={Routes.MOVE_VIEW}>
                <MoveView {...props}/>
            </Route>
            <Route exact path={Routes.MOVE_INDEX}>
                <MoveIndex {...props}/>
            </Route>
        </Switch>
    );
}
