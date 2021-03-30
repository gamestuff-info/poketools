import loadComponent from '../common/loadComponent';
import {Route, Switch} from 'react-router-dom';
import {Routes} from '../routes';

const CaptureRateCalc = loadComponent(() => import('./CaptureRateCalc'));

export default function MoveController(props: {}) {
    return (
        <Switch>
            <Route exact path={Routes.TOOLS_CAPTURE_RATE}>
                <CaptureRateCalc/>
            </Route>
        </Switch>
    );
}
