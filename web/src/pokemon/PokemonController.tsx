import loadComponent from '../common/loadComponent';
import {Route, Switch} from 'react-router-dom';
import {Routes} from '../routes';

const PokemonIndex = loadComponent(() => import('./PokemonIndex'));
const PokemonView = loadComponent(() => import('./PokemonView'));

export default function PokemonController(props: {}) {
    return (
        <Switch>
            <Route exact path={Routes.POKEMON_VIEW}>
                <PokemonView {...props}/>
            </Route>
            <Route exact path={Routes.POKEMON_INDEX}>
                <PokemonIndex {...props}/>
            </Route>
        </Switch>
    );
}
