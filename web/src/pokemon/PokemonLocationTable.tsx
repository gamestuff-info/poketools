import React, {useContext, useMemo, useReducer} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../common/components/Flashes';
import Loading from '../common/components/Loading';
import {Card, Table} from 'react-bootstrap';
import {generatePath, Link} from 'react-router-dom';
import {Routes} from '../routes';
import '../assets/styles/PokemonLocationTable.scss';
import RadialGauge from '../common/components/gauge/RadialGauge';
import EncounterConditionList from './EncounterConditionList';
import LocationMap from '../location/LocationMap';

type PokemonLocationTableRecord = ApiRecord.Pokemon.Encounter.PokemonView;

interface PokemonLocationTableProps {
    pokemon: ApiRecord.Pokemon.Pokemon
}

type EncounterMap = Map<number, Map<number, Array<PokemonLocationTableRecord>>>;

interface PokemonLocationTableState {
    encountersForPokemon?: number
    locations?: Map<number, ApiRecord.Location.LocationInVersionGroup.PokemonView>
    /** Location id > Area id > encounters */
    encounters?: EncounterMap
    loadingEncounters: boolean
    maps?: Map<string, ApiRecord.Location.RegionMap>
    loadingMaps: boolean
}

export default function PokemonLocationTable(props: PokemonLocationTableProps) {
    const {pokemon} = props;
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: PokemonLocationTableState, newState: Partial<PokemonLocationTableState>) => ({...state, ...newState}), {
        loadingEncounters: false,
        loadingMaps: false,
    } as PokemonLocationTableState);
    const {encounters, locations, maps} = state;

    // Reset
    if (encounters !== undefined && state.encountersForPokemon !== undefined && state.encountersForPokemon !== pokemon.id) {
        setState({encounters: undefined, maps: undefined, encountersForPokemon: undefined});
    }

    // Load
    if (!state.loadingEncounters && encounters === undefined) {
        pktQuery<ApiRecord.HydraCollection<PokemonLocationTableRecord>>('encounters', {
            pokemon: pokemon.id,
            version: currentVersion.id,
            groups: ['pokemon_view'],
            pagination: 0,
            'order[chance]': 'DESC',
        }, currentVersion).then(response => {
            const locations: Map<number, ApiRecord.Location.LocationInVersionGroup.PokemonView> = new Map();
            const encounterMap: EncounterMap = new Map();
            for (const encounter of response.data['hydra:member']) {
                const locationId = encounter.locationArea.location.id;
                const areaId = encounter.locationArea.id;
                if (!encounterMap.has(locationId)) {
                    encounterMap.set(locationId, new Map());
                }
                const locationEncounterMap = encounterMap.get(locationId) as Map<number, Array<PokemonLocationTableRecord>>;
                if (!locationEncounterMap.has(areaId)) {
                    locationEncounterMap.set(areaId, []);
                }
                const areaEncounterList = locationEncounterMap.get(areaId) as Array<PokemonLocationTableRecord>;
                areaEncounterList.push(encounter);
                locations.set(locationId, encounter.locationArea.location);
            }

            setState({
                encounters: encounterMap,
                locations: locations,
                loadingEncounters: false,
                encountersForPokemon: pokemon.id,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading encounters.'}]);
        });
        setState({loadingEncounters: true});
    }
    const hasEncounters = useMemo(() => encounters ? encounters.size > 0 : false, [encounters]);
    if (encounters && locations && !state.loadingMaps && maps === undefined) {
        const loadMapUrls: Set<string> = new Set();
        for (const location of locations.values()) {
            if (location.map) {
                loadMapUrls.add(location.map.map);
            }
        }
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Location.RegionMap>>('region_maps', {
            'id': new Array(...loadMapUrls),
        }).then(response => {
            const maps = response.data['hydra:member'];
            maps.sort((a, b) => a.position - b.position);
            setState({
                maps: new Map(maps.map(regionMap => [regionMap['@id'], regionMap])),
                loadingMaps: false,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading encounter maps.'}]);
        });
        setState({loadingMaps: true});
    }

    const encounterCards = [];
    if (encounters && locations) {
        for (const [locationId, locationEncounters] of encounters) {
            encounterCards.push(
                <LocationEncounters
                    key={`encounters-location-${locationId}`}
                    location={locations.get(locationId) as ApiRecord.Location.LocationInVersionGroup.PokemonView}
                    encounters={locationEncounters}
                />,
            );
        }
    }

    return (
        <>
            {(state.loadingEncounters || state.loadingMaps) && <Loading/>}
            {encounters && !hasEncounters && (
                <p>This Pokémon is not encountered in the wild in this version.</p>
            )}
            {encounters && hasEncounters && (
                <>
                    {maps && locations && <EncounterMaps maps={maps} locations={locations}/>}
                    <div className="pkt-pokemon-view-encounters">
                        {encounterCards}
                    </div>
                </>
            )}
        </>
    );
}

type EncounterMapsProps = Required<Pick<PokemonLocationTableState, 'maps' | 'locations'>>;

function EncounterMaps(props: EncounterMapsProps) {
    const {maps, locations} = props;

    // Map region maps to their locations.
    const mapLocations: Map<string, Array<ApiRecord.Location.LocationInVersionGroup.PokemonView>> = useMemo(() => {
        const map = new Map();
        for (const location of locations.values()) {
            if (!location.map) {
                continue;
            }
            if (!map.has(location.map.map)) {
                map.set(location.map.map, []);
            }
            map.get(location.map.map).push(location);
        }
        return map;
    }, [locations]);

    const mapCards = [];
    for (const [mapId, locations] of mapLocations) {
        const map = maps.get(mapId) as ApiRecord.Location.RegionMap;
        mapCards.push((
            // Extra div stops the card from being stretched when maps are different sizes.
            <div key={mapId}>
                <Card>
                    <LocationMap map={map} locations={locations} link={true}/>
                    <Card.Footer>
                        {map.name}
                    </Card.Footer>
                </Card>
            </div>
        ));
    }

    return (
        <div className="pkt-location-index-maps">
            {mapCards}
        </div>
    );
}

interface LocationEncountersProps {
    location: ApiRecord.Location.LocationInVersionGroup.PokemonView,
    encounters: Map<number, Array<PokemonLocationTableRecord>>,
}

function LocationEncounters(props: LocationEncountersProps) {
    const {location, encounters} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const areas = useMemo(() => {
        const areas: Array<ApiRecord.Location.LocationArea.PokemonView> = [];
        for (const areaEncounters of encounters.values()) {
            areas.push(areaEncounters[0].locationArea);
        }
        return areas;
    }, [encounters]);

    return (
        <Card>
            <Card.Header>
                <Card.Title>
                    <Link to={generatePath(Routes.LOCATION_VIEW, {
                        version: currentVersion.slug,
                        location: location.slug,
                    })}>
                        {location.name}
                    </Link>
                </Card.Title>
                <Card.Body>
                    <Table size="sm">
                        <thead>
                        <tr>
                            <th>Method</th>
                            <th>Chance</th>
                            <th>Lv</th>
                            <th>Conditions</th>
                        </tr>
                        </thead>
                        <tbody>
                        {areas.map(area => (
                            <React.Fragment key={area.id}>
                                {/* Area header */}
                                <tr>
                                    <th colSpan={4} className="pkt-pokemon-view-encounters-area">{area.name}</th>
                                </tr>
                                {(encounters.get(area.id) as Array<ApiRecord.Pokemon.Encounter.PokemonView>).map(encounter => (
                                    // Encounter
                                    <tr key={encounter.id}>
                                        <td>
                                            {encounter.method.name}
                                        </td>
                                        <td>
                                            {encounter.chance ? <RadialGauge value={encounter.chance}/> : '*'}
                                        </td>
                                        <td>
                                            {encounter.level && encounter.level}
                                            {!encounter.level && ''}
                                        </td>
                                        <td>
                                            <EncounterConditionList encounter={encounter}/>
                                        </td>
                                    </tr>
                                ))}
                            </React.Fragment>
                        ))}
                        </tbody>
                    </Table>
                </Card.Body>
            </Card.Header>
        </Card>
    );
}
