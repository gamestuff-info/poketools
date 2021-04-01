import React, {useContext, useMemo, useReducer} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import useVersionRedirect from '../common/components/useVersionRedirect';
import setPageTitle from '../common/setPageTitle';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../common/components/Flashes';
import {Breadcrumb, Card, Modal, Tab, Tabs} from 'react-bootstrap';
import Loading from '../common/components/Loading';
import './LocationIndex.scss';
import {AssetPackage, getAssetUrl} from '../common/getAssetUrl';
import LocationMap from './LocationMap';
import {generatePath, Link} from 'react-router-dom';
import {Routes} from '../routes';
import naturalCompare from 'natural-compare';

interface LocationIndexState {
    loadedVersionGroup?: string
    regions?: Array<ApiRecord.Location.Region.LocationIndex> | null
    loadingRegions: boolean
    locations?: Map<string, Array<ApiRecord.Location.LocationInVersionGroup.LocationIndex>> | null
    loadingLocations: boolean
}

export default function LocationIndex(props: {}) {
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: LocationIndexState, newState: Partial<LocationIndexState>) => ({...state, ...newState}), {
        loadingRegions: false,
        loadingLocations: false,
    } as LocationIndexState);
    const {regions, locations} = state;
    setPageTitle('Locations');

    // Reset
    if (state.loadedVersionGroup !== undefined && currentVersion.versionGroup !== state.loadedVersionGroup) {
        setState({loadedVersionGroup: undefined, regions: undefined, locations: undefined});
    }

    // Version redirect
    let redirect;
    if ((redirect = useVersionRedirect(currentVersion as ApiRecord.Version))) {
        return redirect;
    }

    // Load
    if (!state.loadingRegions && regions === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Location.Region.LocationIndex>>('region_in_version_groups', {
            versionGroup: currentVersion.versionGroup,
            groups: ['location_index'],
            pagination: 0,
        }, currentVersion).then((response) => {
            setState({
                regions: response.data['hydra:member'],
                loadingRegions: false,
                loadedVersionGroup: currentVersion.versionGroup,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading Regions.'}]);
        });
        setState({loadingRegions: true});
    }
    if (!state.loadingLocations && regions && locations === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Location.LocationInVersionGroup.LocationIndex>>('location_in_version_groups', {
            versionGroup: currentVersion.versionGroup,
            regions: regions.map(region => region.id),
            groups: ['location_index'],
            pagination: 0,
        }, currentVersion).then((response) => {
            // Map regions to their locations.
            const locations = response.data['hydra:member'];
            locations.sort((a, b) => naturalCompare(a.name, b.name));
            const regionLocations: Map<string, Array<ApiRecord.Location.LocationInVersionGroup.LocationIndex>> = new Map();
            for (const location of locations) {
                if (!regionLocations.has(location.region)) {
                    regionLocations.set(location.region, []);
                }
                (regionLocations.get(location.region) as Array<ApiRecord.Location.LocationInVersionGroup.LocationIndex>).push(location);
            }

            setState({
                locations: regionLocations,
                loadingLocations: false,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading Regions.'}]);
        });
        setState({loadingLocations: true});
    }

    return (
        <div>
            <Breadcrumb>
                <Breadcrumb.Item linkAs="span">{(currentVersion as ApiRecord.Version).name}</Breadcrumb.Item>
                <Breadcrumb.Item active>Locations</Breadcrumb.Item>
            </Breadcrumb>

            <h1>Locations</h1>
            {(state.loadingRegions || state.loadingLocations) && <Loading/>}
            {regions && locations && regions.length > 1 && (
                <Tabs defaultActiveKey={regions[0].slug}>
                    {regions.map(region => (
                        <Tab key={region.id} eventKey={region.slug} title={region.name}>
                            <h2>{region.name}</h2>
                            <LocationList region={region}
                                          locations={locations.get(region['@id']) as Array<ApiRecord.Location.LocationInVersionGroup.LocationIndex>}/>
                        </Tab>
                    ))}
                </Tabs>
            )}
            {regions && locations && regions.length === 1 && (
                <LocationList region={regions[0]}
                              locations={locations.get(regions[0]['@id']) as Array<ApiRecord.Location.LocationInVersionGroup.LocationIndex>}/>
            )}
        </div>
    );
}

interface LocationListProps {
    region: ApiRecord.Location.Region.LocationIndex
    locations: Array<ApiRecord.Location.LocationInVersionGroup.LocationIndex>
}

function LocationList(props: LocationListProps) {
    const {region, locations} = props;
    const rootLocations = useMemo(() => locations.filter((location) => !location.superLocation), [locations]);
    return (
        <>
            <MapList maps={region.maps} locations={locations}/>

            <ul className="pkt-location-index-list">
                {rootLocations.map(location => <LocationListTree key={location.id} location={location}/>)}
            </ul>
        </>
    );
}

function LocationListTree(props: { location: ApiRecord.Location.LocationInVersionGroup.LocationIndex }) {
    const {location} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    return (
        <li>
            <Link to={generatePath(Routes.LOCATION_VIEW, {
                version: currentVersion.slug,
                location: location.slug,
            })}>
                {location.name}
            </Link>
            {location.subLocations.length > 0 && (
                <ul>
                    {location.subLocations.map(subLocation => (
                        <LocationListTree key={subLocation.id} location={subLocation}/>
                    ))}
                </ul>
            )}
        </li>
    );
}

interface MapListState {
    shownMapId: number | null
}

interface MapListProps {
    maps: Array<ApiRecord.Location.RegionMap>,
    locations: Array<ApiRecord.Location.LocationInVersionGroup.LocationIndex>
}

function MapList(props: MapListProps) {
    const {maps, locations} = props;
    const [state, setState] = useReducer((state: MapListState, newState: Partial<MapListState>) => ({...state, ...newState}), {
        shownMapId: null,
    } as MapListState);
    const mapLocations = useMemo(() => {
        const mapLocations = new Map();
        for (const location of locations) {
            if (!location.map) {
                continue;
            }
            if (!mapLocations.has(location.map.map)) {
                mapLocations.set(location.map.map, []);
            }
            mapLocations.get(location.map.map).push(location);
        }
        return mapLocations;
    }, [locations]);

    return (
        <>
            {/* Thumbnails */}
            <div className="pkt-location-index-maps">
                {maps.map(map => (
                    // Extra div stops the card from being stretched when maps are different sizes.
                    <div key={map.id}>
                        <Card>
                            <Card.Img variant="top"
                                      src={getAssetUrl(`map/${map.url}`, AssetPackage.MEDIA)}
                                      role="button"
                                      onClick={() => setState({shownMapId: map.id})}
                            />
                            <Card.Footer>
                                {map.name}
                            </Card.Footer>
                        </Card>
                    </div>
                ))}
            </div>

            {/* Big maps */}
            {maps.map(map => (
                <Modal key={`big-${map.id}`}
                       centered
                       show={state.shownMapId === map.id}
                       onHide={() => setState({shownMapId: null})}
                >
                    <Modal.Header closeButton>
                        {map.name}
                    </Modal.Header>
                    <Modal.Body>
                        <LocationMap map={map}
                                     locations={mapLocations.get(map['@id'])}
                                     link={true}
                                     className='pkt-location-index-map'
                        />
                    </Modal.Body>
                </Modal>
            ))}
        </>
    );
}
