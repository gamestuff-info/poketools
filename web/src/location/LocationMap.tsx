import React, {useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import '../assets/styles/LocationMap.scss';
import {AssetPackage, getAssetUrl} from '../common/getAssetUrl';
import {generatePath} from 'react-router-dom';
import {Routes} from '../routes';
import {OverlayTrigger, Tooltip} from 'react-bootstrap';

interface LocationMapProps extends Record<string, any> {
    map: ApiRecord.Location.RegionMap
    /** All locations must use the map from `map` */
    locations: Array<ApiRecord.Location.LocationInVersionGroup & ApiRecord.Location.LocationInVersionGroup.WithLocationMap>
    /** Link to location? */
    link?: boolean
    /** Show tooltips on map? */
    tooltip?: boolean
}

/**
 * Draw location overlays on a map, optionally linking.
 * @param props
 */
export default function LocationMap(props: LocationMapProps) {
    const {map, locations} = props;
    const link = props.link ?? false;
    const tooltip = props.tooltip ?? true;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const classes = props.className ? props.className.split(' ') : [];
    classes.unshift('pkt-map');

    // Sanity check that all passed maps refer to the same image
    if (!locations.every(location => !location.effectiveMap || location.effectiveMap.map === map['@id'])) {
        throw Error(`All locations must use the same map.`);
    }

    // Sort locations by their Z order
    locations.sort((a, b) => {
        if (!a.effectiveMap) {
            return -1;
        } else if (!b.effectiveMap) {
            return 1;
        }
        return a.effectiveMap.zIndex - b.effectiveMap.zIndex;
    });

    return (
        <svg className={classes.join(' ')}
             viewBox={`0 0 ${map.width} ${map.height}`}
             preserveAspectRatio="xMidYMid meet"
             width={map.width}
             height={map.height}
             xmlns="http://www.w3.org/2000/svg">
            <image className="pkt-map-map" href={getAssetUrl(`map/${map.url}`, AssetPackage.MEDIA)}/>
            {locations.map(location => (
                (location.effectiveMap && (
                    <g key={location.id}>
                        {link && (
                            <a href={generatePath(Routes.LOCATION_VIEW, {
                                version: currentVersion.slug,
                                location: location.slug,
                            })}>
                                <MapOverlay map={location.effectiveMap}
                                            title={location.name}
                                            tooltip={tooltip}
                                />
                            </a>
                        )}
                        {!link && (
                            <MapOverlay map={location.effectiveMap}
                                        title={location.name}
                                        tooltip={tooltip}
                            />
                        )}
                    </g>
                ))
            ))}
        </svg>
    );
}

function MapOverlay(props: { map: ApiRecord.Location.LocationMap, title: string, tooltip?: boolean }) {
    const {map, title} = props;
    const tooltip = props.tooltip ?? true;
    if (!map.overlay) {
        return null;
    }
    //  Not really inner HTML (actually an SVG fragment), but same idea
    const overlay = (<g className="pkt-map-overlay" dangerouslySetInnerHTML={{__html: map.overlay}}/>);

    if (tooltip) {
        return (
            <OverlayTrigger overlay={
                <Tooltip id="map-location">{title}</Tooltip>
            } placement="auto">
                {overlay}
            </OverlayTrigger>
        );
    }

    return overlay;
}
