import {Button, Jumbotron} from 'react-bootstrap';
import {AssetPackage, getAssetUrl} from '../common/getAssetUrl';
import {generatePath, Link, Route, Switch} from 'react-router-dom';
import loadComponent from '../common/loadComponent';
import {Routes} from '../routes';
import NotFound from '../common/components/NotFound';
import React, {useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import Loading from '../common/components/Loading';
import appConfig from '../config.json';

const CreditsPage = loadComponent(() => import('./CreditsPage'));

function FrontPage(props: {}) {
    const {currentVersion} = useContext(AppContext) as AppContextProps;

    return (
        <div>
            <Jumbotron>
                <div className="d-flex flex-column flex-md-row justify-content-around align-items-center">
                    <img className="pkt-logo" src={getAssetUrl('logo-cropped.svg', AssetPackage.MEDIA)}
                         alt=""/>
                    <div className="d-flex flex-column">
                        <h1 className="display-4">This is Pokétools.</h1>
                        <p className="lead">
                            Accurate tools and data tailored to each version.
                        </p>
                    </div>
                </div>

                <Button variant="primary"
                        size="lg"
                        disabled={!currentVersion}
                        as={Link}
                        to={generatePath(Routes.SEARCH, {version: currentVersion ? currentVersion.slug : appConfig.defaultVersionSlug})}
                >
                    {!currentVersion && <Loading uncontained label=""/>}
                    Search
                </Button>
            </Jumbotron>

            <h1>What?</h1>
            <p>This is a site dedicated to sharing information about various Pokémon games.</p>

            <h1>Why?</h1>
            <p>
                There are certainly many sites around dedicated to this purpose. However many of them are only accurate
                to the latest version, or aren't organized in a clear manner. This site's goal is to present each game's
                data as a "snapshot" &ndash; guaranteed to be accurate to that game when it was released.
            </p>

            <h1>How?</h1>
            <p>
                Navigate the site using the navigation menu, or try searching!
            </p>
        </div>
    );
}

export default function FrontController(props: {}) {
    return (
        <Switch>
            <Route exact path={Routes.FRONT_FRONT}>
                <FrontPage/>
            </Route>
            <Route exact path={Routes.FRONT_CREDITS}>
                <CreditsPage/>
            </Route>
            <Route path="*">
                <NotFound/>
            </Route>
        </Switch>
    );
}
