import './assets/styles/App.scss';
import React, {useReducer} from 'react';
import {matchPath, Route, Switch} from 'react-router-dom';
import {pktQuery} from './common/client';
import Navbar from './main/Navbar';
import Flashes, {Flash, FlashSeverity} from './common/components/Flashes';
import Footer from './main/Footer';
import loadComponent from './common/loadComponent';
import {AxiosError} from 'axios';
import {ControllerRouteBases, dexRouteBase} from './routes';
import Loading from './common/components/Loading';
import appConfig from './config.json';
import AppContext from './common/Context';
import MathJax from 'mathjax3-react';

const AbilityController = loadComponent(() => import('./ability/AbilityController'));
const FrontController = loadComponent(() => import('./front/FrontController'));
const ItemController = loadComponent(() => import('./item/ItemController'));
const LocationController = loadComponent(() => import('./location/LocationController'));
const MoveController = loadComponent(() => import('./move/MoveController'));
const NatureController = loadComponent(() => import('./nature/NatureController'));
const PokemonController = loadComponent(() => import('./pokemon/PokemonController'));
const TypeController = loadComponent(() => import('./type/TypeController'));
const ToolsController = loadComponent(() => import('./tools/ToolsController'));

interface AppState {
    currentVersionId?: number,
    versions?: Map<number, ApiRecord.Version> | null,
    loadingVersions: boolean
    flashes: Array<Flash>
}

export default function App(props: {}) {
    // Check the version in the URL to set an initial version
    const routeMatch = matchPath(document.location.pathname, dexRouteBase);
    let currentVersionSlug: string | null = null;
    if (routeMatch) {
        currentVersionSlug = (routeMatch.params as Record<string, string>).version;
    }
    const [state, setState] = useReducer((state: AppState, newState: Partial<AppState>) => ({...state, ...newState}), {
        loadingVersions: false,
        flashes: [],
    } as AppState);
    const setFlashes = React.useCallback((flashes: Array<Flash>) => setState({flashes: flashes}), []);

    // Load versions
    if (!state.loadingVersions && state.versions === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Version>>('versions', {pagination: 0})
            .then((response) => {
                // Get a map keyed by version id
                const members = response.data['hydra:member'];
                const versions = new Map(members.map((version: ApiRecord.Version) => [version.id, version]));
                const newState: Partial<AppState> = {versions: versions, loadingVersions: false};
                if (state.currentVersionId === undefined) {
                    let currentVersion = null;
                    const checkSlug = currentVersionSlug ?? appConfig.defaultVersionSlug;
                    for (const version of versions.values()) {
                        if (version.slug === checkSlug) {
                            currentVersion = version;
                            break;
                        }
                    }
                    if (currentVersion === null) {
                        throw new Error(`Default version ${appConfig.defaultVersionSlug} is not found in versions.`);
                    }
                    newState.currentVersionId = currentVersion.id;
                }
                setState(newState);
            }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{
                severity: FlashSeverity.DANGER,
                message: 'The site is not available. Check your internet connection and try again.',
            }]);
        });
        setState({loadingVersions: true});
    }
    const isReady = state.versions && state.currentVersionId;
    const setVersion = (newVersionId: number) => {
        // Version has changed
        if (!state.versions) {
            return;
        }
        setState({currentVersionId: newVersionId});
    };

    // Build context
    const currentVersion = state.versions?.get(state.currentVersionId ?? 0);
    const appContext = {
        currentVersion: currentVersion,
        setFlashes: setFlashes,
    };
    return (
        <div className="content-wrapper">
            <AppContext.Provider value={appContext}>
                <MathJax.Provider
                    url="https://cdn.jsdelivr.net/npm/mathjax@3/es5/mml-chtml.js"
                    options={{
                        chtml: {
                            mathmlSpacing: true,
                        },
                    }}
                >
                    <Navbar currentVersion={currentVersion}
                            versions={state.versions}
                            onVersionChange={setVersion}
                    />

                    <Flashes flashes={state.flashes}/>

                    <main className="container">
                        <Switch>
                            <Route path={ControllerRouteBases.ABILITY}>
                                {!isReady && <Loading/>}
                                {isReady && <AbilityController/>}
                            </Route>
                            <Route path={ControllerRouteBases.ITEM}>
                                {!isReady && <Loading/>}
                                {isReady && <ItemController/>}
                            </Route>
                            <Route path={ControllerRouteBases.LOCATION}>
                                {!isReady && <Loading/>}
                                {isReady && <LocationController/>}
                            </Route>
                            <Route path={ControllerRouteBases.MOVE}>
                                {!isReady && <Loading/>}
                                {isReady && <MoveController/>}
                            </Route>
                            <Route path={ControllerRouteBases.NATURE}>
                                {!isReady && <Loading/>}
                                {isReady && <NatureController/>}
                            </Route>
                            <Route path={ControllerRouteBases.POKEMON}>
                                {!isReady && <Loading/>}
                                {isReady && <PokemonController/>}
                            </Route>
                            <Route path={ControllerRouteBases.TYPE}>
                                {!isReady && <Loading/>}
                                {isReady && <TypeController/>}
                            </Route>

                            <Route path={ControllerRouteBases.TOOLS}>
                                {!isReady && <Loading/>}
                                {isReady && <ToolsController/>}
                            </Route>
                            <Route path="/">
                                {/* This controller, as the default controller, also handles NotFound. */}
                                <FrontController/>
                            </Route>
                        </Switch>
                    </main>

                    <footer>
                        <Footer/>
                    </footer>
                </MathJax.Provider>
            </AppContext.Provider>
        </div>
    );
}
