import {FlashSeverity} from '../common/components/Flashes';
import useVersionRedirect from '../common/components/useVersionRedirect';
import {Breadcrumb, Card, Tab, Tabs} from 'react-bootstrap';
import React, {useContext, useReducer} from 'react';
import {generatePath, Link, useParams} from 'react-router-dom';
import {Routes} from '../routes';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import NotFound from '../common/components/NotFound';
import Loading from '../common/components/Loading';
import setPageTitle from '../common/setPageTitle';
import AppContext, {AppContextProps} from '../common/Context';
import LocationMap from './LocationMap';
import PktMarkdown from '../common/components/PktMarkdown';
import ShopItemTable from './ShopItemTable';
import LocationPokemonTable from './LocationPokemonTable';

interface LocationViewState {
    location?: ApiRecord.Location.LocationInVersionGroup.LocationView | null
    loadingLocation: boolean
}

export default function LocationView(props: {}) {
    // Setup
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const {location: locationSlug} = useParams<{ version: string, location: string }>();
    const [state, setState] = useReducer((state: LocationViewState, newState: Partial<LocationViewState>) => ({...state, ...newState}), {
        loadingLocation: false,
    } as LocationViewState);
    const {location} = state;
    let redirect;
    if ((redirect = useVersionRedirect(currentVersion))) {
        return redirect;
    }

    // Reset
    if (location && (locationSlug !== location.slug || currentVersion.versionGroup !== location.versionGroup)) {
        setState({location: undefined});
    }

    // Load
    if (location === null) {
        return (<NotFound/>);
    } else if (!state.loadingLocation && (location === undefined || (location && location.versionGroup !== currentVersion.versionGroup))) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Location.LocationInVersionGroup.LocationView>>('location_in_version_groups', {
            versionGroup: currentVersion.versionGroup,
            slug: locationSlug,
            page: 1,
            itemsPerPage: 1,
            groups: ['location_view'],
        }, currentVersion).then((response) => {
            if (response.data['hydra:member'].length === 0) {
                setState({
                    location: null,
                    loadingLocation: false,
                });
            } else {
                setState({
                    location: response.data['hydra:member'][0],
                    loadingLocation: false,
                });
            }
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading Location.'}]);
        });
        setState({loadingLocation: true});
    } else if (location) {
        setPageTitle(['Locations', location.name]);
    }

    return (
        <div>
            <Breadcrumb>
                <Breadcrumb.Item linkAs="span">{currentVersion.name}</Breadcrumb.Item>
                <Breadcrumb.Item linkAs={Link}
                                 linkProps={{to: generatePath(Routes.LOCATION_INDEX, {version: currentVersion.slug})}}>
                    Locations
                </Breadcrumb.Item>
                <Breadcrumb.Item active>
                    {!location && <Loading uncontained/>}
                    {location && location.name}
                </Breadcrumb.Item>
            </Breadcrumb>

            {state.loadingLocation && <Loading/>}
            {location && (
                <div>
                    <h1>{location.name}</h1>
                    <LocationRegionMap location={location}/>

                    {/* Sub-locations */}
                    {location.subLocations.length > 0 && (
                        <div>
                            <h2>Sub-locations</h2>
                            <SubLocationList location={location}/>
                        </div>
                    )}

                    {/* Description, if applicable */}
                    {location.description && <PktMarkdown>{location.description}</PktMarkdown>}

                    {/* Areas */}
                    <AreaList location={location}/>
                </div>
            )}
        </div>
    );
}

interface LocationRegionMapState {
    regionMap?: ApiRecord.Location.RegionMap | null
    loadingRegionMap: boolean
}

function LocationRegionMap(props: { location: ApiRecord.Location.LocationInVersionGroup.LocationView }) {
    const {location} = props;
    const {setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: LocationRegionMapState, newState: Partial<LocationRegionMapState>) => ({...state, ...newState}), {
        loadingRegionMap: false,
    } as LocationRegionMapState);
    const {regionMap} = state;
    if (!location.effectiveMap) {
        return null;
    }
    if (!state.loadingRegionMap && (regionMap === undefined || (regionMap && regionMap['@id'] !== location.effectiveMap.map))) {
        pktQuery<ApiRecord.Location.RegionMap>(location.effectiveMap.map).then(response => {
            setState({
                regionMap: response.data,
                loadingRegionMap: false,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading map.'}]);
        });
        setState({
            regionMap: undefined,
            loadingRegionMap: true,
        });
    }

    return (
        <div className="d-flex justify-content-around">
            <Card>
                {state.loadingRegionMap && <Loading uncontained/>}
                {regionMap && (
                    <>
                        <Card.Img as={LocationMap}
                                  variant="top"
                                  map={regionMap}
                                  locations={[location]}
                        />
                        <Card.Footer>
                            {regionMap.name}
                        </Card.Footer>
                    </>
                )}
            </Card>
        </div>
    );
}

function SubLocationList(props: { location: ApiRecord.Location.LocationInVersionGroup.LocationView }) {
    const {location} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    return (
        <ul>
            {location.subLocations.map(subLocation => (
                <li key={subLocation['@id']}>
                    <Link to={generatePath(Routes.LOCATION_VIEW, {
                        version: currentVersion.slug,
                        location: subLocation.slug,
                    })}>
                        {subLocation.name}
                    </Link>
                </li>
            ))}
        </ul>
    );
}

interface AreaListProps {
    location: ApiRecord.Location.LocationInVersionGroup.LocationView
    areas?: Array<ApiRecord.Location.LocationArea.LocationView>
}

function AreaList(props: AreaListProps) {
    const {location} = props;
    const areas = props.areas ?? location.areas;
    if (areas.length === 0) {
        return null;
    } else if (areas.length > 1) {
        const defaultArea = areas.find(area => area.isDefault) ?? areas[0];
        return (
            <Tabs defaultActiveKey={defaultArea['@id']}>
                {areas.map(area => (
                    <Tab key={area['@id']} eventKey={area['@id']} title={area.name}>
                        <AreaDetails location={location} area={area}/>
                    </Tab>
                ))}
            </Tabs>
        );
    }
    return (<AreaDetails location={location} area={areas[0]}/>);
}

interface AreaDetailsProps {
    location: ApiRecord.Location.LocationInVersionGroup.LocationView
    area: ApiRecord.Location.LocationArea.LocationView
}

function AreaDetails(props: AreaDetailsProps) {
    const {location, area} = props;
    return (
        <div>
            {area.shops.length > 0 && (
                <div>
                    <h3>Shops</h3>
                    <AreaShops area={area}/>
                </div>
            )}

            <h3>Pokémon</h3>
            <LocationPokemonTable area={area}/>

            {area.treeChildren.length > 0 && <AreaList location={location} areas={area.treeChildren}/>}
        </div>
    );
}

function AreaShops(props: { area: ApiRecord.Location.LocationArea.LocationView }) {
    const {area} = props;
    const {shops} = area;
    return (
        <>
            {shops.map(shop => (
                <div key={shop['@id']}>
                    <h4>{shop.name}</h4>
                    <ShopItemTable shop={shop}/>
                </div>
            ))}
        </>
    );
}
