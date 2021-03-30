import {useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import Teaser from '../search/Teaser';
import {generatePath} from 'react-router-dom';
import {Routes} from '../routes';
import PktMarkdown from '../common/components/PktMarkdown';
import LocationMap from './LocationMap';
import './LocationTeaser.scss';

export default function LocationTeaser(props: { location: ApiRecord.Location.LocationInVersionGroup.SearchResult }) {
    const {location} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const image = location.effectiveMap && location.regionMap ? (
        <LocationMap map={location.regionMap} locations={[location]}/>) : undefined;

    return (
        <Teaser label={location.name}
                image={image}
                imageWidth={2}
                href={generatePath(Routes.LOCATION_VIEW, {version: currentVersion.slug, location: location.slug})}
                description={<PktMarkdown>{location.shortDescription ?? ''}</PktMarkdown>}
        />
    );
}
